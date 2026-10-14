<?php

namespace App\DataTables;

use App\Support\Icon;
use Yajra\DataTables\Facades\DataTables;

class DataTableBuilder
{
    public function build($query, array $config = [])
    {
        $with = $config['with'] ?? [];
        $editColumns = $config['editColumns'] ?? [];
        $addColumns = $config['addColumns'] ?? [];
        $rawColumns = $config['rawColumns'] ?? [];

        if ($with) {
            $query = $query->with($with);
        }

        $table = DataTables::of($query)->addIndexColumn();

        foreach ($editColumns as $column => $callback) {
            $table->editColumn($column, $callback);
        }
        foreach ($addColumns as $column => $callback) {
            $table->addColumn($column, $callback);
        }

        return $table->rawColumns(array_values(array_unique($rawColumns)))->make(true);
    }

    public function statusBadge($text, $color)
    {
        $tone = str_replace('btn-', '', $color);

        if (!in_array($tone, ['success', 'danger', 'warning', 'primary', 'info', 'secondary', 'dark'], true)) {
            $tone = 'secondary';
        }

        return '<span class="badge badge-light-' . $tone . ' badge-status">'
            . '<span class="badge-status-dot"></span>' . ucwords($text) . '</span>';
    }

    public function thumbnail($directory, $file)
    {
        if (!$file) {
            return 'Image not available';
        }
        return '<div class="d-flex align-items-center">
            <div class="symbol symbol-45px me-5">
                <img alt="thumbnail" src="' . asset($directory . '/' . $file) . '">
            </div>
        </div>';
    }

    public function actionButtons(array $btns)
    {
        return count($btns) ? implode($btns) : null;
    }

    public function editIconSvg()
    {
        return Icon::svg('pencil', 16);
    }

    public function deleteIconSvg()
    {
        return Icon::svg('trash', 16);
    }

    public function icon($name, $class = '')
    {
        return Icon::svg($name, 16, $class);
    }

    public function editButton($url, $title = '')
    {
        return '<a ' . ($title ? 'title="' . $title . '"' : '') . ' href="' . $url . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">'
            . $this->editIconSvg() . '</a>';
    }

    public function deleteButton($uid, $name, $title = '', $extraAttributes = '')
    {
        return '<a href="#" ' . ($title ? 'title="' . $title . '"' : '') . ' data-bs-target="#delete_modal" data-bs-toggle="modal" data-modal_id="' . $uid . '" data-modal_name="' . $name . '" data-modal_title="Delete ' . $name . '?" ' . $extraAttributes . ' class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">'
            . $this->deleteIconSvg() . '</a>';
    }
}
