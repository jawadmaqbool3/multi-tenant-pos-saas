<?php

namespace App\Dashboard;

interface Widget
{
    /**
     * Globally unique, dot-namespaced key, e.g. "payroll.pending".
     */
    public function key(): string;

    /**
     * Human readable widget title.
     */
    public function title(): string;

    /**
     * Render kind: "card" | "chart" | "table" | "custom".
     */
    public function type(): string;

    /**
     * RBAC permission name required to see this widget.
     */
    public function permission(): string;

    /**
     * Permission-module name used for plan-feature gating. An empty string
     * means the widget is never plan-gated.
     */
    public function module(): string;

    /**
     * Data scope: "company" | "own" | "platform".
     */
    public function scope(): string;

    /**
     * Bootstrap grid column hint, e.g. "col-md-6 col-xl-4".
     */
    public function size(): string;

    /**
     * Sort order within the dashboard (ascending).
     */
    public function order(): int;

    /**
     * Client renderer key. Defaults to the widget type. Widgets with bespoke
     * JavaScript declare their own renderer name.
     */
    public function renderer(): string;

    /**
     * Extra runtime visibility check beyond permission/plan, e.g. the
     * attendance widget only shows for users with attendance enabled.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     */
    public function visible($user): bool;

    /**
     * JSON payload for the client, or null when the widget hydrates itself
     * (custom widgets such as the stock report or attendance card).
     */
    public function data(): ?array;

    /**
     * Blade partial that renders the widget container.
     */
    public function view(): string;
}
