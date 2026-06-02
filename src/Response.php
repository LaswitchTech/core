<?php

// Declaring namespace
namespace LaswitchTech\Core;

/**
 * Controller Response
 *
 * Return value from controller actions. Replaces direct echo/header calls.
 * Provides opt-out of default rendering (render → redirect → json → error).
 */
class Response
{
    // Response types
    const TYPE_RENDER = 'render';
    const TYPE_REDIRECT = 'redirect';
    const TYPE_JSON = 'json';
    const TYPE_ERROR = 'error';

    // Properties
    public string $type;
    public int $status;
    public array $headers;
    public mixed $content;

    // For render responses
    public ?string $template = null;
    public ?string $view = null;
    public array $data = [];

    // For redirect responses
    public ?string $url = null;

    // For error responses
    public ?string $message = null;

    /**
     * Create a render response (template + view)
     *
     * @param string $template
     * @param string|null $view
     * @param array $data
     * @return self
     */
    public static function render(string $template, ?string $view = null, array $data = []): self
    {
        $response = new self();
        $response->type = self::TYPE_RENDER;
        $response->status = 200;
        $response->template = $template;
        $response->view = $view;
        $response->data = $data;
        $response->content = null;
        $response->headers = [];
        return $response;
    }

    /**
     * Create a redirect response
     *
     * @param string $url
     * @param int $status
     * @return self
     */
    public static function redirect(string $url, int $status = 302): self
    {
        $response = new self();
        $response->type = self::TYPE_REDIRECT;
        $response->status = $status;
        $response->url = $url;
        $response->content = null;
        $response->headers = [];
        return $response;
    }

    /**
     * Create a JSON response
     *
     * @param mixed $content
     * @param int $status
     * @param array $headers
     * @return self
     */
    public static function json(mixed $content, int $status = 200, array $headers = []): self
    {
        $response = new self();
        $response->type = self::TYPE_JSON;
        $response->status = $status;
        $response->content = $content;
        $response->headers = array_merge([
            'Content-Type: application/json; charset=utf-8',
        ], $headers);
        return $response;
    }

    /**
     * Create an error response (maps to HTTP status code)
     *
     * @param int $status
     * @param string $message
     * @param string|null $template
     * @param string|null $view
     * @return self
     */
    public static function error(int $status, string $message = '', ?string $template = null, ?string $view = null): self
    {
        $response = new self();
        $response->type = self::TYPE_ERROR;
        $response->status = $status;
        $response->message = $message;
        $response->template = $template;
        $response->view = $view;
        $response->content = null;
        $response->headers = [];
        return $response;
    }

    /**
     * Send the response (output headers and content)
     *
     * @return void
     */
    public function send(): void
    {
        // Set status code
        if ($this->status >= 400) {
            http_response_code($this->status);
        }

        // Set headers
        if (!empty($this->headers)) {
            foreach ($this->headers as $header) {
                header($header);
            }
        }

        // Handle by type
        switch ($this->type) {
            case self::TYPE_REDIRECT:
                header('Location: ' . $this->url);
                exit;

            case self::TYPE_JSON:
                if (is_array($this->content) || is_object($this->content)) {
                    $this->content = json_encode($this->content, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                }
                echo $this->content;
                exit;

            case self::TYPE_RENDER:
                // Content is handled by the View engine / controller
                // Nothing to output here — the controller renders template+view
                break;

            case self::TYPE_ERROR:
                // Content is handled by the View engine for the error template
                break;
        }
    }

    /**
     * Check if this response requires early termination
     *
     * @return bool
     */
    public function terminates(): bool
    {
        return in_array($this->type, [self::TYPE_REDIRECT, self::TYPE_JSON]);
    }
}
