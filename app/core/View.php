<?php

namespace Aurora\Core;

final class View
{
    /**
     * Data for the current context
     * @var array
     */
    private array $data = [];

    /**
     * Parent template
     * @var string
     */
    private string $parent = '';

    /**
     * Inside the parent template
     * @var bool
     */
    private bool $inside_parent = false;

    /**
     * List of sections
     * @var array
     */
    private array $sections = [];

    /**
     * Sections stack to keep track of the current section in the template
     * @var array
     */
    private array $sections_stack = [];

    public function __construct(private string $templates_dir, private ?object $helper = null)
    {
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
     * @param [bool] $handle_parent process the parent template or not
     * @return string the template output
     */
    public function get(string $template, array $data = [], bool $handle_parent = true): string
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

        if ($this->parent && $handle_parent) {
            $this->inside_parent = true;
            $output = $this->get($this->parent, $this->data, false);
            $this->inside_parent = false;
        }

        return $output;
    }

    /**
     * Returns the template output with the current template data
     * @param string $template the template
     * @param [array] $data additional template data
     * @return string the template output
     */
    protected function include(string $template, array $data = []): string
    {
        return $this->get($template, array_merge($this->data, $data), false);
    }

    /**
     * Sets the parent template for the current template
     * @param string $parent the parent template
     */
    protected function extend(string $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * Starts a new section
     * @param string $name the section name
     */
    protected function sectionStart(string $name): void
    {
        $this->sections_stack[] = $name;
        ob_start();
    }

    /**
     * Ends the latest section in the stack
     */
    protected function sectionEnd(): void
    {
        $output = ob_get_contents();
        ob_end_clean();

        $section = array_pop($this->sections_stack);

        if ($this->inside_parent) {
            echo $this->sections[$section] ?? $output;
        } else {
            $this->sections[$section] = $output;
        }
    }
}
