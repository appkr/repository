<?php

namespace Appkr\Repository\Eloquent;

use Appkr\Repository\Conditions\Condition;
use Appkr\Repository\Contracts\RepositoryInterface;
use Appkr\Repository\Exceptions\RepositoryException;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Container\Container as App;
use Illuminate\Events\Dispatcher;

/**
 * Class Repository
 */
abstract class Repository implements RepositoryInterface
{
    /**
     * @var App
     */
    private $app;

    /**
     * @var
     */
    protected $model;

    /**
     * @var Dispatcher
     */
    protected $event;

    /**
     * @var array
     */
    protected $eagerLoads = [];

    /**
     * Repository
     *
     * @param App        $app
     * @param Collection $collection
     * @param Dispatcher $event
     *
     * @throws RepositoryException
     */
    public function __construct(App $app, Collection $collection, Dispatcher $event)
    {
        $this->app   = $app;
        $this->event = $event;

        $this->setModel();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public abstract function model();

    /**
     * Get all of the models from the database.
     *
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function all($columns = ['*'])
    {
        $model = ($eagerLoads = $this->getEagerLoads())
            ? $this->model->with($eagerLoads)
            : $this->model;

        return $model->get($columns);
    }

    /**
     * Get an array with the values of a given column.
     *
     * @param  string $value
     * @param  string $key
     *
     * @return array
     */
    public function lists($value, $key = null)
    {
        return $this->isL51()
            ? $this->model->lists($value, $key)->toArray()
            : $this->model->lists($value, $key);
    }

    /**
     * Paginate the given query.
     *
     * @param int   $perPage
     * @param array $columns
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 1, $columns = ['*'])
    {
        $model = ($eagerLoads = $this->getEagerLoads())
            ? $this->model->with($eagerLoads)
            : $this->model;

        return $model->paginate($perPage, $columns);
    }

    /**
     * Save a new model and return the instance.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data)
    {
        $this->event->fire('repository.creating');

        return $this->model->create($data);
    }

    /**
     * Update the model in the database.
     *
     * @param array  $data
     * @param        $id
     * @param string $attribute
     *
     * @return bool|int
     */
    public function update(array $data, $id, $attribute = "id")
    {
        $model = $this->model->where($attribute, '=', $id);

        $this->event->fire('repository.updating', [$model]);

        return $model->update($data);
    }

    /**
     * Delete the model from the database.
     *
     * @param $id
     *
     * @return bool|null
     */
    public function delete($id)
    {
        if (! ($model = $this->model->find($id))) {
            return false;
        }

        $this->event->fire('repository.deleting', [$model]);

        return $model->delete();
    }

    /**
     * Find a model by its primary key.
     *
     * @param       $id
     * @param array $columns
     *
     * @return Model|null
     */
    public function find($id, $columns = ['*'])
    {
        $model = ($eagerLoads = $this->getEagerLoads())
            ? $this->model->with($eagerLoads)
            : $this->model;

        return $model->find($id, $columns);
    }

    /**
     * Find a model by the given criteria.
     *
     * @param       $attribute
     * @param       $value
     * @param array $columns
     *
     * @return Model|null
     */
    public function findBy($attribute, $value, $columns = ['*'])
    {
        $model = ($eagerLoads = $this->getEagerLoads())
            ? $this->model->with($eagerLoads)
            : $this->model;

        return $model->where($attribute, '=', $value)->first($columns);
    }

    /**
     * Find a collection of models by the given criterion.
     *
     * @param       $attribute
     * @param       $value
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findAllBy($attribute, $value, $columns = ['*'])
    {
        $model = ($eagerLoads = $this->getEagerLoads())
            ? $this->model->with($eagerLoads)
            : $this->model;

        return $model->where($attribute, '=', $value)->get($columns);
    }

    /**
     * Find a collection of models by the given query conditions.
     *
     * @param array $conditions
     * @param array $columns
     * @param bool  $or
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findWhere($conditions, $columns = ['*'], $or = false)
    {
        $model = ($eagerLoads = $this->getEagerLoads())
            ? $this->model->with($eagerLoads)
            : $this->model;

        foreach ($conditions as $field => $value) {
            if ($value instanceof \Closure) {
                $model = (! $or)
                    ? $model->where($value)
                    : $model->orWhere($value);
            } elseif (is_array($value)) {
                if (count($value) === 3) {
                    list($field, $operator, $search) = $value;

                    $model = (! $or)
                        ? $model->where($field, $operator, $search)
                        : $model->orWhere($field, $operator, $search);
                } elseif (count($value) === 2) {
                    list($field, $search) = $value;

                    $model = (! $or)
                        ? $model->where($field, '=', $search)
                        : $model->orWhere($field, '=', $search);
                }
            } else {
                $model = (! $or)
                    ? $model->where($field, '=', $value)
                    : $model->orWhere($field, '=', $value);
            }
        }

        return $model->get($columns);
    }

    /**
     * Factory - new up the model and set the model property
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws RepositoryException
     */
    protected function setModel()
    {
        $model = $this->app->make($this->model());

        if (! $model instanceof Model) {
            throw new RepositoryException(
                "Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model"
            );
        }

        return $this->model = $model;
    }

    /**
     * Attach orderBy clause
     *
     * @param $column
     * @param $direction
     *
     * @return $this
     */
    public function setOrder($column, $direction = 'asc')
    {
        if (! $column) {
            return $this;
        }

        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * Wrapper for setOrder()
     *
     * @param        $column
     * @param string $direction
     *
     * @return Repository
     */
    public function orderBy($column, $direction = 'asc')
    {
        return $this->setOrder($column, $direction);
    }

    /**
     * Helper - Set descending order
     *
     * @param $column
     *
     * @return Repository
     */
    public function latest($column)
    {
        return $this->setOrder($column, 'desc');
    }

    /**
     * Helper - Set ascending order
     *
     * @param $column
     *
     * @return Repository
     */
    public function oldest($column)
    {
        return $this->setOrder($column, 'asc');
    }

    /**
     * Set query conditions
     *
     * @param array|Condition $conditions
     *
     * @return $this
     */
    public function setConditions($conditions)
    {
        if ($conditions instanceof \Closure) {
            $this->model = call_user_func($conditions, $this->model);
        } elseif (is_array($conditions)) {
            foreach ($conditions as $condition) {
                static::setConditions($condition);
            }
        } elseif ($conditions instanceof Condition) {
            $this->applyCondition($conditions);
        }

        return $this;
    }

    /**
     * Eagerload setter
     *
     * @param string $eagerLoads
     *
     * @return $this
     */
    public function setEagerLoads($eagerLoads = null)
    {
        if (is_array($eagerLoads)) {
            $this->eagerLoads = $eagerLoads;
        } elseif (is_string($eagerLoads)) {
            $this->eagerLoads[] = $eagerLoads;
        }

        return $this;
    }

    /**
     * Wrapper for setEagerLoads
     *
     * @param string $eagerLoads
     *
     * @return Repository
     */
    public function with($eagerLoads = null)
    {
        return $this->setEagerLoads($eagerLoads);
    }

    /**
     * Eagerload getter
     *
     * @return array
     */
    public function getEagerLoads()
    {
        return $this->eagerLoads;
    }

    /**
     * Set query conditions to the current model.
     *
     * @param Condition $condition
     */
    protected function applyCondition(Condition $condition)
    {
        $this->model = $condition->applyTo($this->model);
    }

    /**
     * Determine if the framework is 5.1 or higher
     *
     * @return bool
     */
    private function isL51()
    {
        return substr($this->app->version(), 0, 3) >= 5.1;
    }
}