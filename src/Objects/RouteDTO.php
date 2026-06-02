<?php

// Declaring namespace
namespace LaswitchTech\Core\Objects;

/**
 * Route Data Transfer Object
 *
 * Pure data object — no globals, no rendering, no persistence.
 * Properties map directly to routes.cfg JSON keys.
 */
class RouteDTO
{
    // Route identification
    public string $namespace;
    public ?string $template = null;
    public ?string $view = null;

    // Access control
    public bool $public = true;
    public int $level = 0;

    // Controller dispatch
    public ?string $action = null;

    // Navigation / metadata
    public ?string $parent = null;
    public array $location = [];
    public ?string $label = null;
    public ?string $icon = null;
    public ?string $color = null;

    /**
     * Create a RouteDTO from routes.cfg JSON data
     *
     * @param string $namespace The route path (e.g., "/dashboard")
     * @param array|null $data The route metadata array from routes.cfg
     */
    public function __construct(string $namespace, ?array $data = null)
    {
        $this->namespace = $namespace;

        if ($data !== null) {
            $this->template = $data['template'] ?? null;
            $this->view     = $data['view'] ?? null;
            $this->public   = $data['public'] ?? true;
            $this->level    = $data['level'] ?? 0;
            $this->action   = $data['action'] ?? null;
            $this->parent   = $data['parent'] ?? null;
            $this->location = $data['location'] ?? [];
            $this->label    = $data['label'] ?? null;
            $this->icon     = $data['icon'] ?? null;
            $this->color    = $data['color'] ?? null;
        }
    }

    /**
     * Convert this DTO to a routes.cfg-compatible array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'template' => $this->template,
            'view'     => $this->view,
            'public'   => $this->public,
            'action'   => $this->action,
            'location' => $this->location,
            'level'    => $this->level,
            'parent'   => $this->parent,
            'label'    => $this->label,
            'icon'     => $this->icon,
            'color'    => $this->color,
        ];
    }

    /**
     * Check if this route requires authentication
     *
     * @return bool
     */
    public function isPrivate(): bool
    {
        return !$this->public;
    }
}
