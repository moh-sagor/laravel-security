<?php

namespace Sagor\LaravelSecurity\Support;

class CompatibilityHelper
{
    /**
     * Get Laravel version string.
     *
     * @return string
     */
    public static function getLaravelVersion(): string
    {
        if (defined('\Illuminate\Foundation\Application::VERSION')) {
            return \Illuminate\Foundation\Application::VERSION;
        }

        return '5.5.0';
    }

    /**
     * Create a JSON response compatible across all Laravel versions (5.5 to 13.x).
     *
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public static function jsonResponse(array $data, int $status = 403, array $headers = [])
    {
        if (function_exists('response')) {
            return response()->json($data, $status, $headers);
        }

        return new \Illuminate\Http\JsonResponse($data, $status, $headers);
    }
}
