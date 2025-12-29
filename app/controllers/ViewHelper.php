<?php

namespace Aurora\App;

final class ViewHelper
{
    private const WORDS_PER_MINUTE = 210;

    private ?string $default_date_format = null;

    private ?\Aurora\Core\Language $language = null;

    public function __construct(string $default_date_format = null, \Aurora\Core\Language $language = null)
    {
        $this->default_date_format = $default_date_format;
        $this->language = $language;
    }

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
     * @param [string|null] $date_format the date format
     * @return string the given timestamp formatted
     */
    public function dateFormat(mixed $tstamp, ?string $date_format = null): string
    {
        static $formatter = null;

        if ($formatter === null) {
            $formatter = new \IntlDateFormatter($this->getLanguageCode(), 0, 0);
        }

        $formatter->setTimeZone(\Aurora\App\Setting::get('timezone'));
        $formatter->setPattern($date_format ?? $this->default_date_format);
        return $formatter->format($tstamp);
    }

    /**
     * Returns the language key or all of them if no key is specified
     * @param [string] $key the key to obtain
     * @param [bool] $escape escape the language key or not
     * @return mixed the language key/keys
     */
    public function t(?string $key = null, bool $escape = true)
    {
        $text = $this->language->get($key);
        return $key && $escape ? e($text) : $text;
    }

    /**
     * @see \Aurora\Core\Language::getCode
     */
    public function getLanguageCode(): string
    {
        return $this->language->getCode();
    }

    /**
     * @see \Aurora\Core\Helper::getUrl
     */
    public function url(string $path = ''): string
    {
        return \Aurora\Core\Helper::getUrl($path);
    }
}
