<?php

namespace Aurora\App;

final class ViewHelper
{
    private const WORDS_PER_MINUTE = 210;

    public function getFileQuery(string $file): string
    {
        return "$file?v=" . filemtime(\Aurora\System\Helper::getPath($file));
    }

    public function getReadTime(string $str): int
    {
        return max(1, round(str_word_count($str) / self::WORDS_PER_MINUTE));
    }

    public function getContentUrl($url): string
    {
        $host = parse_url($url, PHP_URL_HOST);

        return $host && $host !== $_SERVER['HTTP_HOST']
            ? $url
            : \Aurora\System\Helper::getUrl(\Aurora\System\Kernel::config('content') . '/' . trim($url, '/'));
    }

    public function dateFormat($tstamp): string
    {
        static $formatter = null;

        if ($formatter === null) {
            $formatter = new \IntlDateFormatter(\Aurora\System\Container::get('language')->getCode(), 0, 0);
        }

        $formatter->setPattern(\Aurora\App\Setting::get('date_format') ?? '');
        return $formatter->format($tstamp);
    }
}
