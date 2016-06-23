<?php
namespace ApiFoo\Api;

use \ApiFoo\Interfaces\OrmInterface;
use \ApiFoo\Api\ApiRequest;

class ApiRepository
{
    private $orm;

    public function __construct(OrmInterface $orm)
    {
        $this->orm = $orm;
    }

    /**
     * Count all results, independent of pagination.
     */
    public function executeCountQuery($query, ApiRequest $apiRequest)
    {
        $repository = $this->orm->getRepositoryFromQuery($query);
        $countQuery = $this->orm->getCountQuery($repository);

        $this->addFilterCriteria($countQuery, $repository, $apiRequest->getFilters());
        $count = $this->orm->executeQuery($countQuery)[0]['count'];
        return $count;
    }

    /**
     * Split the ids query, otherwise pagination will create issues when using joins.
     */
    public function executeIdsQuery($query, ApiRequest $apiRequest)
    {
        $repository = $this->orm->getRepositoryFromQuery($query);
        $idsQuery = $this->orm->getIdsQuery($repository);
        
        $this->addFilterCriteria($idsQuery, $repository, $apiRequest->getFilters());
        $this->addSortCriteria($idsQuery, $repository, $apiRequest->getSort());
        $this->addPageCriteria($idsQuery, $repository, $apiRequest->getPageSize(), $apiRequest->getPageNumber());
        $ids = array_map(function($item) {
            return $item['id'];
        }, $this->orm->executeQuery($idsQuery));
        return $ids;
    }

    /**
     * Get the data.
     * Filtering and pagination already done in previous query, so only sort now.
     */
    public function executeListQuery($query, ApiRequest $apiRequest, $ids)
    {
        $repository = $this->orm->getRepositoryFromQuery($query);
        $listQuery = $this->orm->getListQuery($query, $ids);

        $this->addSortCriteria($listQuery, $repository, $apiRequest->getSort());
        $results = $this->orm->executeQuery($listQuery);
        return $results;
    }

    /**
     * Get the data.
     */
    public function executeItemQuery($query, ApiRequest $apiRequest)
    {
        $itemQuery = $query;
        $repository = $this->orm->getRepositoryFromQuery($itemQuery);
        $this->addFilterCriteria($itemQuery, $repository, $apiRequest->getFilters());
        $results = $this->orm->executeQuery($itemQuery);
        return $results;
    }

    public function getList($query, ApiRequest $apiRequest)
    {
        $count = $this->executeCountQuery($query, $apiRequest);
        if ($count == 0) {
            return [
                'data' => [],
                'meta' => ['count' => 0, 'pages' => 0],
            ];
        }

        $ids = $this->executeIdsQuery($query, $apiRequest);
        $results = $this->executeListQuery($query, $apiRequest, $ids);

        return [
            'data' => $results,
            'meta' => [
                'count' => $count,
                'pages' => $apiRequest->getPageSize() === 0 ? 1 : ceil($count/$apiRequest->getPageSize()),
            ],
        ];
    }

    public function getItem($query, ApiRequest $apiRequest)
    {
        $results = $this->executeItemQuery($query, $apiRequest);

        $data = null;
        if (count($results) == 1) {
            $data = $results[0];
        }

        return [
            'data' => $data,
            'meta' => [
            ],
        ];
    }

    public function addFilterCriteria($query, $repository, array $filters)
    {
        foreach ($filters as $name => $value) {
            if ($value === '') {
                continue;
            }
            $repository->{'add'.ucfirst($name).'Filter'}($query, $value);
        }
    }

    public function addSortCriteria($query, $repository, $sort)
    {
        foreach ($sort as $name => $order) {
            $repository->{'add'.ucfirst($name).'Sort'}($query, $order);
        }
    }

    public function addPageCriteria($query, $repository, $pageSize, $pageNumber)
    {
        if ($pageSize > 0) {
            $this->orm->setPagination($query, $pageSize, $pageNumber);
            return true;
        }
        return false;
    }
}