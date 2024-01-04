<?php

namespace Aurora\System;

final class View
{
    private array $data = [];

    public function __construct(private string $templates_dir, private ?object $helper = null)
    {
        $this->templates_dir = $templates_dir;
    }

    /**
     * Fallback to helper object
     */
    public function __call($name, $args)
    {
        return call_user_func_array([ $this->helper, $name ], $args);
    }

    /**
     * Returns the template output
     * @param string $template the template
     * @param [array] $data the template data
     * @return string the template output
     */
    public function get(string $template, array $data = []): string
    {
        $this->data = $data;
        extract($data, EXTR_SKIP);
        unset($data);

        ob_start();

        try {
            require $this->templates_dir . "/$template";
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Returns the template output with the current template data
     * @param string $template the template
     * @return string the template output
     */
    protected function include(string $template): string
    {
        return $this->get($template, $this->data);
    }
}
