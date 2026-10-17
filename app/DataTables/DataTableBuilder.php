<?php

namespace App\DataTables;

use App\Support\Icon;
use Yajra\DataTables\Facades\DataTables;

class DataTableBuilder
{
    public function build($query, array $config = [])
    {
        $this->ensureColumnsInput();

        $with = $config['with'] ?? [];
        $editColumns = $config['editColumns'] ?? [];
        $addColumns = $config['addColumns'] ?? [];
        $filterColumns = $config['filterColumns'] ?? [];
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
        foreach ($filterColumns as $column => $callback) {
            $table->filterColumn($column, $callback);
        }

        return $table->rawColumns(array_values(array_unique($rawColumns)))->make(true);
    }

    protected function ensureColumnsInput()
    {
        $request = request();

        if ($request->input('columns') !== null) {
            return;
        }

        $request->query->set('columns', []);
        $request->request->set('columns', []);
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

    public function letterAvatar($name)
    {
        return '<div class="d-flex align-items-center">
            <div class="symbol symbol-45px me-5">
                <span class="symbol-label bg-light-primary text-primary fw-bold">'
            . e($this->initials($name)) .
            '</span>
            </div>
        </div>';
    }

    protected function initials($name)
    {
        $initials = '';

        foreach (preg_split('/\s+/', trim((string) $name)) as $word) {
            if ($word === '') {
                continue;
            }

            $initials .= mb_substr($word, 0, 1);

            if (mb_strlen($initials) >= 2) {
                break;
            }
        }

        return $initials === '' ? '?' : mb_strtoupper($initials);
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

    public function viewButton($url, $title = '')
    {
        return '<a ' . ($title ? 'title="' . $title . '"' : '') . ' href="' . $url . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">'
            . $this->icon('eye') . '</a>';
    }

    public function deleteButton($uid, $name, $title = '', $extraAttributes = '')
    {
        return '<a href="#" ' . ($title ? 'title="' . $title . '"' : '') . ' data-bs-target="#delete_modal" data-bs-toggle="modal" data-modal_id="' . $uid . '" data-modal_name="' . $name . '" data-modal_title="Delete ' . $name . '?" ' . $extraAttributes . ' class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">'
            . $this->deleteIconSvg() . '</a>';
    }
}
