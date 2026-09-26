<?php

namespace Sagor\LaravelSecurity\Support;

use Illuminate\Http\Request;

class PayloadNormalizer
{
    /**
     * Normalize all HTTP request inputs (query, post, json, route params) into a flat array.
     *
     * @param Request $request
     * @return array
     */
    public static function normalize(Request $request): array
    {
        $payload = [];

        // 1. Query parameters
        foreach ($request->query() as $key => $val) {
            static::flatten('query.' . $key, $val, $payload);
        }

        // 2. Form POST / Body parameters
        foreach ($request->post() as $key => $val) {
            static::flatten('body.' . $key, $val, $payload);
        }

        // 3. JSON payload
        if ($request->isJson()) {
            $json = $request->json()->all();
            if (is_array($json)) {
                foreach ($json as $key => $val) {
                    static::flatten('json.' . $key, $val, $payload);
                }
            }
        }

        // 4. Route parameters
        try {
            if ($request->route()) {
                $routeParams = method_exists($request->route(), 'parameters') ? $request->route()->parameters() : [];
                foreach ($routeParams as $key => $val) {
                    if (is_string($val) || is_numeric($val)) {
                        $payload['route.' . $key] = (string) $val;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore route resolution errors
        }

        return $payload;
    }

    /**
     * Recursively flatten multidimensional data structures into dot-notated key-value strings.
     *
     * @param string $prefix
     * @param mixed $data
     * @param array &$result
     * @return void
     */
    protected static function flatten(string $prefix, $data, array &$result)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                static::flatten($prefix . '.' . $k, $v, $result);
            }
        } elseif (is_scalar($data)) {
            $result[$prefix] = (string) $data;
        }
    }
}
