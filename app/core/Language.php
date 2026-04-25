<?php

namespace Aurora\Core;

class Language
{
    public function __construct(private array $languages = [], private string $code = '')
    {
        $this->languages = $languages;
        $this->code = $code;
    }

    /**
     * Returns the language key or all of them if no key is specified
     * @throws \InvalidArgumentException
     * @param [string] $key the key to obtain
     * @return mixed the language key/keys
     */
    public function get(?string $key = null): mixed
    {
        $current_language = $this->languages[$this->code] ?? null;
        if (!isset($key)) {
            return $current_language;
        }

        if (is_array($current_language) && array_key_exists($key, $current_language)) {
            return $current_language[$key];
        }

        throw new \InvalidArgumentException("Language with code '$key' does not exist");
    }

    /**
     * Sets the language to use
     * @param string $code the language code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * Returns the code of the language that is in use
     * @return string the language code
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Returns an array of all the available languages
     * @return array the available languages
     */
    public function getAll(): array
    {
        return array_keys($this->languages);
    }
}
