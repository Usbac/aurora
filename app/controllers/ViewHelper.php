<?php

namespace Aurora\App;

final class ViewHelper
{
    private const WORDS_PER_MINUTE = 210;

    /**
     * Returns the url or path to obtain a file without unnecessary cache
     * @param string $filename the file url or path
     * @return string the url or path to obtain a file without unnecessary cache
     */
    public function getFileQuery(string $filename): string
    {
        return "$filename?v=" . filemtime(\Aurora\Core\Helper::getPath($filename));
    }

    /**
     * Returns the time in minutes required to read the given string
     * @param string $str the string
     * @return int the reading time
     */
    public function getReadTime(string $str): int
    {
        return max(1, round(str_word_count($str) / self::WORDS_PER_MINUTE));
    }

    /**
     * Returns the url of the given content
     * @param mixed $url the url
     * @return string the url
     */
    public function getContentUrl(mixed $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);

        return $host && $host !== $_SERVER['HTTP_HOST']
            ? $url
            : \Aurora\Core\Helper::getUrl(\Aurora\Core\Kernel::config('content') . '/' . trim($url, '/'));
    }

    /**
     * Returns the given timestamp formatted
     * @param mixed $tstamp the timestamp
     * @return string the given timestamp formatted
     */
    public function dateFormat(mixed $tstamp): string
    {
        static $formatter = null;

        if ($formatter === null) {
            $formatter = new \IntlDateFormatter(\Aurora\Core\Container::get('language')->getCode(), 0, 0);
        }

        $formatter->setPattern(\Aurora\App\Setting::get('date_format') ?? '');
        return $formatter->format($tstamp);
    }

    /**
     * @see \Aurora\Core\Helper::getUrl
     */
    public function url(string $path = ''): string
    {
        return \Aurora\Core\Helper::getUrl($path);
    }

    /**
     * Returns the current CSRF token, it creates it if it's not set
     * @return string the CSRF token
     */
    public function csrfToken(): string
    {
        if (!isset($_COOKIE['csrf_token'])) {
            $token = bin2hex(random_bytes(8));

            $_COOKIE['csrf_token'] = $token;
            setcookie('csrf_token', $token, [
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        return $_COOKIE['csrf_token'];
    }
}
