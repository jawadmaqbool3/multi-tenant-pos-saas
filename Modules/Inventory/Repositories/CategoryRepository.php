<?php

namespace Modules\Inventory\Repositories;

use App\Repositories\BaseRepository;

use Modules\Inventory\Models\Category;

class CategoryRepository extends BaseRepository
{
    protected $dataTableRawColumns = ['action', 'thumbnail', 'status'];

    public function __construct()
    {
        parent::__construct(new Category());

        $this->dataTableEditColumns = [
            'thumbnail' => function ($category) {
                return $this->dt->thumbnail('assets/media/categories/thumbs', $category->thumbnail);
            },
            'status' => function ($category) {
                if ($category->status == 'published') {
                    $color = 'btn-success';
                    $text = $category->status;
                } elseif ($category->status == 'unpublished') {
                    $color = 'btn-warning';
                    $text = $category->status;
                } else {
                    $color = 'btn-danger';
                    $text = $category->status . ' (' . date('d M, Y h:i A', strtotime($category->published_date)) . ')';
                }
                return $this->dt->statusButton($text, $color);
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($category) {
                $btns = [];
                if (auth()->user()->hasPermission('edit category')) {
                    $btns[] = $this->dt->editButton(route('categories.edit', $category->uid));
                }
                if (auth()->user()->hasPermission('delete category')) {
                    $btns[] = $this->dt->deleteButton($category->uid, $category->name);
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }
}
