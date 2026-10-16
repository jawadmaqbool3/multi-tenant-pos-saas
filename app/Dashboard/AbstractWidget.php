<?php

namespace App\Dashboard;

abstract class AbstractWidget implements Widget
{
    public function module(): string
    {
        return '';
    }

    public function scope(): string
    {
        return 'company';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-4';
    }

    public function order(): int
    {
        return 0;
    }

    public function renderer(): string
    {
        return $this->type();
    }

    public function visible($user): bool
    {
        return true;
    }

    /**
     * Domain payload for the widget. Return null to opt out of the batched
     * payload (used by self-hydrating custom widgets).
     */
    protected function payload(): ?array
    {
        return [];
    }

    public function data(): ?array
    {
        $payload = $this->payload();

        if ($payload === null) {
            return null;
        }

        return array_merge([
            'type' => $this->type(),
            'renderer' => $this->renderer(),
            'key' => $this->key(),
            'title' => $this->title(),
        ], $payload);
    }

    public function view(): string
    {
        if (in_array($this->type(), ['card', 'chart', 'table'], true)) {
            return 'dashboard.widgets.' . $this->type();
        }

        return 'dashboard.widgets.card';
    }
}
