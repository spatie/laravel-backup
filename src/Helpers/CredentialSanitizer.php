<?php

namespace Spatie\Backup\Helpers;

class CredentialSanitizer
{
    /**
     * Sanitize exception messages to remove sensitive credentials.
     */
    public static function sanitizeMessage(string $message): string
    {
        // Pattern to match common credential formats in connection strings and URLs
        $patterns = [
            // MySQL/PostgreSQL connection strings: password=secret or password='secret' or password="secret"
            '/password\s*=\s*["\']?[^"\'\s;]+["\']?/i' => 'password=***',
            // URLs with credentials: user:password@host
            '/:([^:@\s]+)@/i' => ':***@',
            // Environment variable patterns
            '/DB_PASSWORD\s*=\s*.+/i' => 'DB_PASSWORD=***',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $message = preg_replace($pattern, $replacement, $message);
        }

        return $message;
    }

    /**
     * Sanitize exception object by replacing its message with a sanitized version.
     */
    public static function sanitizeException(\Throwable $exception): string
    {
        $sanitizedMessage = self::sanitizeMessage($exception->getMessage());
        $trace = self::sanitizeStackTrace($exception->getTraceAsString());

        return $sanitizedMessage . PHP_EOL . $trace;
    }

    /**
     * Sanitize stack trace to remove credentials.
     */
    protected static function sanitizeStackTrace(string $trace): string
    {
        return self::sanitizeMessage($trace);
    }
}
