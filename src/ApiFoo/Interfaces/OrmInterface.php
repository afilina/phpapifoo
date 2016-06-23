<?php
namespace ApiFoo\Interfaces;

interface OrmInterface
{
    function __construct($table);
    function setPagination($query, $pageSize, $pageNumber);
    function getCountQuery();
    function getIdsQuery();
    function getListQuery($query, $ids);
    function getItemQuery($query);
    function executeQuery($query);
    /**
     * The repository is the class that will contain all the filters and sorting associated with this query.
     */
    function getRepositoryFromQuery($query);
}