<?php

namespace App\Repositories;

use App\DataTables\DataTableBuilder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected $model;
    protected $dt;

    protected $dataTableWith = [];
    protected $dataTableEditColumns = [];
    protected $dataTableAddColumns = [];
    protected $dataTableFilterColumns = [];
    protected $dataTableRawColumns = ['action'];

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->dt = new DataTableBuilder();
    }

    public function dataTable($query = null, array $config = [])
    {
        $query = $query ?: $this->model->newQuery();

        return $this->dt->build($query, [
            'with' => $config['with'] ?? $this->dataTableWith,
            'editColumns' => array_merge($this->dataTableEditColumns, $config['editColumns'] ?? []),
            'addColumns' => array_merge($this->dataTableAddColumns, $config['addColumns'] ?? []),
            'filterColumns' => array_merge($this->dataTableFilterColumns, $config['filterColumns'] ?? []),
            'rawColumns' => array_merge($this->dataTableRawColumns, $config['rawColumns'] ?? []),
        ]);
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record;
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function query()
    {
        return $this->model->newQuery();
    }
}
