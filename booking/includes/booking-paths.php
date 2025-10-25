<?php
// Shared helper for computing Booking module URLs and filesystem paths
if (!function_exists('booking_initialize_paths')) {
    /**
     * Initialise cached base path and directory values for the booking module.
     */
    function booking_initialize_paths(): void
    {
        if (isset($GLOBALS['booking_base_path'], $GLOBALS['booking_base_dir'])) {
            return;
        }

        $bookingDirectoryReal = realpath(__DIR__ . '/..');
        if ($bookingDirectoryReal === false) {
            $bookingDirectoryReal = dirname(__DIR__);
        }
        $bookingDirectory = rtrim(str_replace('\\', '/', $bookingDirectoryReal), '/');

        $documentRootReal = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
        $documentRoot = $documentRootReal !== false ? rtrim(str_replace('\\', '/', $documentRootReal), '/') : null;

        $basePath = '/pms/booking/';
        if ($documentRoot && strpos($bookingDirectory, $documentRoot) === 0) {
            $relativePath = substr($bookingDirectory, strlen($documentRoot));
            $relativePath = '/' . ltrim(str_replace('\\', '/', $relativePath), '/');
            if ($relativePath === '/' || $relativePath === '') {
                $basePath = '/';
            } else {
                $basePath = rtrim($relativePath, '/') . '/';
            }
        }

        $GLOBALS['booking_base_path'] = rtrim($basePath, '/') . '/';
        $GLOBALS['booking_base_dir'] = $bookingDirectory . '/';
    }

    function booking_base(): string
    {
        booking_initialize_paths();
        return $GLOBALS['booking_base_path'];
    }

    function booking_base_dir(): string
    {
        booking_initialize_paths();
        return $GLOBALS['booking_base_dir'];
    }

    function booking_url(string $relative = ''): string
    {
        booking_initialize_paths();
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        if (!empty($_SERVER['REQUEST_SCHEME'])) {
            $scheme = $_SERVER['REQUEST_SCHEME'];
        }
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = rtrim($GLOBALS['booking_base_path'], '/') . '/' . ltrim($relative, '/');
        $path = '/' . ltrim($path, '/');
        return $scheme . '://' . $host . $path;
    }

    function booking_asset(string $relative = ''): string
    {
        return booking_url('assets/' . ltrim($relative, '/'));
    }
}
