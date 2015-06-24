<?php

namespace Bosnadev\Repositories\Contracts;

/**
 * Interface RepositoryInterface
 *
 * @package Bosnadev\Repositories\Contracts
 */
interface RepositoryInterface
{

    /**
     * Set query conditions
     *
     * @param array|\Bosnadev\Repositories\Conditions\Condition $conditions
     *
     * @return $this
     */
    public function setConditions($conditions);

    /**
     * Get all of the models from the repository.
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*']);

    /**
     * Get an array with the values of a given column.
     *
     * @param  string $value
     * @param  string $key
     *
     * @return array
     */
    public function lists($value, $key = null);

    /**
     * Paginate the given query.
     *
     * @param int   $perPage
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate($perPage = 1, $columns = ['*']);

    /**
     * Save a new model and return the instance.
     *
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update the model in the repository.
     *
     * @param array $data
     * @param       $id
     *
     * @return mixed
     */
    public function update(array $data, $id);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function delete($id);

    /**
     * Find a model by its primary key.
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * Find a model by the given criteria.
     *
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findBy($field, $value, $columns = ['*']);

    /**
     * Find a collection of models by the given criterion.
     *
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findAllBy($field, $value, $columns = ['*']);

    /**
     * Find a collection of models by the given query conditions.
     *
     * @param       $conditions
     * @param array $columns
     * @param bool  $or
     *
     * @return mixed
     */
    public function findWhere($conditions, $columns = ['*'], $or = false);

    /**
     * Attach orderBy clause
     *
     * @param $column
     * @param $direction
     *
     * @return $this
     */
    public function setOrder($column, $direction = 'asc');
}