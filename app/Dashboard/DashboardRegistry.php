<?php

namespace App\Dashboard;

class DashboardRegistry
{
    /**
     * @var array<string, class-string<Widget>>
     */
    protected array $widgets = [];

    /**
     * Register a widget class. Keyed by the widget's own key() so a later
     * registration of the same key replaces the earlier one.
     */
    public function register(string $widgetClass): void
    {
        if (!is_subclass_of($widgetClass, Widget::class)) {
            throw new \InvalidArgumentException($widgetClass . ' must implement ' . Widget::class);
        }

        /** @var Widget $widget */
        $widget = app($widgetClass);
        $this->widgets[$widget->key()] = $widgetClass;
    }

    /**
     * @return array<string, Widget>
     */
    public function all(): array
    {
        $resolved = [];

        foreach ($this->widgets as $key => $class) {
            $resolved[$key] = app($class);
        }

        return $resolved;
    }

    public function has(string $key): bool
    {
        return isset($this->widgets[$key]);
    }

    public function get(string $key): ?Widget
    {
        return $this->has($key) ? app($this->widgets[$key]) : null;
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->widgets);
    }
}
