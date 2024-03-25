<?php

namespace Medboubazine\Pay\Core\Helpers;

class HttpRequest
{
    /**
     * Cache headers
     *
     * @var array|null
     */
    protected static ?array  $headers = null;

    /**
     * Get current request headers
     *
     * @return array
     */
    public static function headers(): array
    {
        if (static::$headers) {
            return static::$headers;
        }
        $server_headers = [];
        foreach ($_SERVER as $key => $value) {
            if (Str::startsWith($key, "HTTP_")) {
                $header_name = Str::lower(Str::replace(['HTTP_', "_"], ['', '-'], $key));
                $header_name = ucwords($header_name, '-');

                $server_headers[$header_name] = $value;
            }
        }

        return static::$headers = $server_headers;
    }
    /**
     * get header
     *
     * @param string $name
     * @return string|null
     */
    public static function header(string $name): ?string
    {
        $headers = self::headers();

        return $headers[$name] ?? null;
    }
    /**
     * Get request data
     *
     * @return array|null
     */
    public static function data(): array
    {
        if (!empty($_GET)) {
            return $_GET;
        }
        if (!empty($_POST)) {
            return $_POST;
        }
        if (!empty($_REQUEST)) {
            return $_REQUEST;
        }
        return [];
    }
    /**
     * Body
     *
     * @return string|null
     */
    public static function body(): ?string
    {
        $body = file_get_contents('php://input');
        return (!empty($body)) ? $body : null;
    }
}
