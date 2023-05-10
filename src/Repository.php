<?php

namespace Webbundels\Models;

use Illuminate\Database\Eloquent\Model;

abstract class Repository {

    protected $model;

    public function __construct(Model $model) 
    {
        $this->model = $model;
    }

    public function get(array $filters = [], array $with = [])
    {
        return (new $this->model)->filter($filters)->withs($with)->get();
    }
    
    public function first(array $filters = [], array $with = [])
    {
        return (new $this->model)->filter($filters)->withs($with)->first();
    }

    public function getAll(array $with = [])
    {
        return (new $this->model)->withs($with)->get();
    }

    public function getById($id, array $with = [])
    {
        return $this->first(['id' => $id], $with);
    }

    public function getByIds($ids, array $with = [])
    {
        return $this->get(['ids' => $ids], $with);
    }

    public function getByName($name, array $with = [])
    {
        return $this->first(['name' => $name], $with);
    }
    
    public function pluckIds(array $filters = []) 
    {
        return $this->pluck('id', $filters);
    }

    public function pluck($column, array $filters = [])
    {
        if (is_array($column)) {
            return (new $this->model)->filter($filters)->select($column[0], $column[1])->pluck($column[0], $column[1]);
        }

        return (new $this->model)->filter($filters)->select($column)->pluck($column);
    }

    public function pluckWithKeys(string $column, string $key, array $filters = [])
    {
        return (new $this->model)->filter($filters)->pluck($column, $key);
    }
    
    public function count(array $filters = []) 
    {
        return (new $this->model)->filter($filters)->count();
    }

    public function paginate(array $filters = [], array $with = [])
    {
        return (new $this->model)->filter($filters)->withs($with)->paginate($filters['per_page']);
    }

    public function increment($column, array $filters = [], $amount = 1)
    {
        return (new $this->model)->filter($filters)->increment($column, $amount);
    }

    public function decrement($column, array $filters = [], $amount = 1)
    {
        return (new $this->model)->filter($filters)->decrement($column, $amount);
    }

    public function store(array $data, array $with = [])
    {
        $model = (new $this->model);
        $model->fill($data);
        $model->save();

        if ($with) {
            $model = (new $this->model)->where('id', $model->id)->withs($with)->first();
        }
        
        return $model;
    }

    public function storeMany(array $data)
    {
        (new $this->model)->insert($data);
    }

    public function firstOrStore(array $data, array $delayed_data = []) {
        return (new $this->model)->firstOrCreate($data, $delayed_data);
    }

    public function updateOrCreate(array $data, array $delayed_data = []) {
        return (new $this->model)->updateOrCreate($data, $delayed_data);
    }

    public function update($id, array $data, array $with = [])
    {
        // Get traits from model
        $uses = $this->getTraitsFromModel();

        if (is_array($id)) {
            $model = (new $this->model)->whereIn('id', $id);
            if (in_array('SoftDeletes', $uses)) {
                $model = $model->withTrashed();
            }
            $model = $model->update($data);

            if ($with) {
                $model = $this->getByIds($model->pluck('id'), $with);
            }
        } else {
            $model = (new $this->model);
            if (in_array('SoftDeletes', $uses, true)) {
                $model = $model->withTrashed();
            }
            $model = $model->find($id);
            $model->fill($data);
            $model->save();

            if ($with) {
                $model = $this->getById($id, $with);
            }
        }

        return $model;
    }

    public function updateFiltered($filters, array $data, array $with = [])
    {
        $uses = $this->getTraitsFromModel();

        $models = (new $this->model)->filter($filters);
        if (in_array('SoftDeletes', $uses)) {
            $models = $models->withTrashed();
        }
        $models = $models->update($data);

        if ($with) {
            $models = $this->get($models, $with);
        }

        return $models;
    }

    public function destroy($id, array $with = [], $forceDelete = false)
    {
        if (is_array($id)) {
            return $this->destroyMany($id);
        }

        $filters = ['id_is' => $id];
        $uses = $this->getTraitsFromModel();
        if (in_array('SoftDeletes', $uses, true)) {
            $filters[] = 'with_trashed';
        }

        $model = $this->first($filters, $with);
        if ($forceDelete) {
            $model->forceDelete();
        } else {
            $model->delete();
        }

        return $model;
    }

    public function destroyMany(array $ids, $forceDelete = false) 
    {
        if ($forceDelete) {
            return (new $this->model)->whereIn('id', $ids)->forceDelete();
        }

        return (new $this->model)->whereIn('id', $ids)->delete();
    }

    public function forceDestroy($id, array $with = [])
    {
        return $this->destroy($id, $with, true);
    }

    protected function getTraitsFromModel()
    {
        $uses = class_uses($this->model);
        $uses = array_map(function($use) {
            $use = explode('\\', $use);
            return end($use);
        }, $uses);

        return $uses;
    }
}