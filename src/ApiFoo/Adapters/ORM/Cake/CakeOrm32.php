<?php
namespace ApiFoo\Adapters\ORM\Cake;

use ApiFoo\Adapters\ORM\OrmInterface;

/**
 * Abstraction layer between the ORM and the ApiRepository that is in charge of issuing the queries.
 */
class CakeOrm32 implements OrmInterface
{
    private $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function setPagination($query, $pageSize, $pageNumber)
    {
        $query->limit($pageSize);
        $query->page($pageNumber);

        return $query;
    }

    public function getCountQuery()
    {
        $this->table->alias('root');
        $query = $this->table->query();

        $query
            ->hydrate(false)
            ->distinct(true)
            ->select(['count' => $query->func()->count('root.id')]);

        return $query;
    }

    public function getIdsQuery()
    {
        $this->table->alias('root');
        $query = $this->table
            ->query()
            ->hydrate(false)
            ->distinct(true)
            ->select(['root.id'])
        ;
        return $query;
    }

    public function getListQuery($query, $ids)
    {
        $query->repository()->alias('root');
        $query->where(['root.id' => $ids], ['root.id' => 'integer[]']);
        return $query;
    }

    public function getItemQuery($query)
    {
        $query->repository()->alias('root');
        return $query;
    }

    public function executeQuery($query)
    {
        $query->repository()->alias('root');
        return $query->toList();
    }

    public function getRepositoryFromQuery($query)
    {
        return $query->repository();
    }
}