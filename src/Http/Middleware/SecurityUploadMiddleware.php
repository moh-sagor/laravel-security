<?php

namespace Sagor\LaravelSecurity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sagor\LaravelSecurity\Models\SecurityEvent;
use Sagor\LaravelSecurity\Support\CompatibilityHelper;
use Sagor\LaravelSecurity\Support\SecuritySanitizer;
use Sagor\LaravelSecurity\Upload\QuarantineManager;
use Sagor\LaravelSecurity\Upload\UploadSecurityManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SecurityUploadMiddleware
{
    /**
     * @var UploadSecurityManager
     */
    protected $uploadManager;

    /**
     * SecurityUploadMiddleware constructor.
     *
     * @param UploadSecurityManager $uploadManager
     */
    public function __construct(UploadSecurityManager $uploadManager)
    {
        $this->uploadManager = $uploadManager;
    }

    /**
     * Handle incoming request file uploads.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->files && count($request->files->all()) > 0) {
            $allFiles = $request->allFiles();
            foreach ($this->flattenFiles($allFiles) as $file) {
                if ($file instanceof UploadedFile) {
                    $result = $this->uploadManager->process($file);
                    if ($result->isInfected()) {
                        // 1. Quarantine file & create record in MalwareScan table
                        if (file_exists($file->getRealPath())) {
                            try {
                                QuarantineManager::quarantine(
                                    $file->getRealPath(),
                                    $file->getClientOriginalName(),
                                    $result->getThreatName(),
                                    'upload_security'
                                );
                            } catch (\Throwable $e) {
                                // Fail open on quarantine error
                            }
                        }

                        // 2. Log SecurityEvent record in database for real-time dashboard & cyber desk
                        try {
                            if (class_exists('Sagor\LaravelSecurity\Models\SecurityEvent')) {
                                SecurityEvent::create([
                                    'event_id' => 'sec_' . uniqid() . '_' . bin2hex(random_bytes(4)),
                                    'type' => 'upload.security',
                                    'severity' => 'critical',
                                    'confidence' => 1.0,
                                    'risk_score' => 95,
                                    'ip_hash' => class_exists('Sagor\LaravelSecurity\Support\SecuritySanitizer')
                                        ? SecuritySanitizer::hashIp($request->ip())
                                        : md5((string) $request->ip()),
                                    'ip_address_encrypted' => function_exists('encrypt') ? encrypt($request->ip()) : $request->ip(),
                                    'user_id' => method_exists($request, 'user') && $request->user() ? $request->user()->getKey() : null,
                                    'route' => substr($request->path(), 0, 255),
                                    'method' => $request->method(),
                                    'user_agent_hash' => md5((string) $request->userAgent()),
                                    'payload_hash' => md5((string) $file->getClientOriginalName()),
                                    'action' => 'block',
                                    'metadata' => json_encode([
                                        'threat_name' => $result->getThreatName(),
                                        'original_filename' => $file->getClientOriginalName(),
                                        'mime_type' => $file->getClientMimeType(),
                                        'file_size' => $file->getSize(),
                                        'reason' => 'Uploaded file rejected by security policy: ' . $result->getThreatName(),
                                    ]),
                                ]);
                            }
                        } catch (\Throwable $e) {
                            // Fail open on database error
                        }

                        if ($request->expectsJson() || $request->is('api/*')) {
                            return CompatibilityHelper::jsonResponse([
                                'message' => 'Uploaded file rejected by security policy: ' . $result->getThreatName(),
                                'code' => 'MALICIOUS_UPLOAD_BLOCKED',
                            ], 422);
                        }

                        if (view()->exists('laravel-security::blocked')) {
                            return response(view('laravel-security::blocked', [
                                'reason' => 'Uploaded file rejected by security policy: ' . $result->getThreatName(),
                                'ip' => $request->ip(),
                            ]), 422);
                        }

                        return response('Uploaded file rejected by security policy.', 422);
                    }
                }
            }
        }

        return $next($request);
    }

    /**
     * Flatten nested file upload arrays.
     *
     * @param array $files
     * @return array
     */
    protected function flattenFiles(array $files): array
    {
        $flattened = [];
        foreach ($files as $file) {
            if (is_array($file)) {
                $flattened = array_merge($flattened, $this->flattenFiles($file));
            } elseif ($file instanceof UploadedFile) {
                $flattened[] = $file;
            }
        }
        return $flattened;
    }
}
