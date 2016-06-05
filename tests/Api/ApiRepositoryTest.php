<?php

use ApiFoo\Api\ApiRequest;
use ApiFoo\Api\ApiRepository;

class ApiRepositoryTest extends \PHPUnit_Framework_TestCase
{
    private $repository;
    private $query;
    private $orm;
    private $apiRequest;

    public function setUp()
    {
        $this->orm = $this->getMock('\ApiFoo\Adapters\ORM\OrmInterface');
        $this->repository = $this->getMock('Table', ['addNameFilter', 'addIdFilter', 'addNameSort', 'addIdSort'], [], 'TestRepository', false);
        $this->query = $this->getMock('Query', null, [], '', false);
    }

    public function testAddFilterCriteria_WithFilters_CallsFilterMethods()
    {
        $apiQuery = $this->getMock('\ApiFoo\Api\ApiRepository', null, [], '', false);
        $filters = [
            'name' => 'A',
            'id' => 1,
        ];

        $this->repository->expects($this->once())->method('addNameFilter');
        $this->repository->expects($this->once())->method('addIdFilter');

        $apiQuery->addFilterCriteria($this->query, $this->repository, $filters);
    }

    public function testAddFilterCriteria_WithEmptyFilter_SkipFilter()
    {
        $apiQuery = $this->getMock('\ApiFoo\Api\ApiRepository', null, [], '', false);
        $filters = [
            'id' => '',
        ];

        $this->repository->expects($this->never())->method('addNIdFilter');

        $apiQuery->addFilterCriteria($this->query, $this->repository, $filters);
    }

    public function testAddSortCriteria_WithSorts_CallsSortMethods()
    {
        $apiQuery = $this->getMock('\ApiFoo\Api\ApiRepository', null, [], '', false);
        $sort = [
            'name' => '-',
            'id' => '',
        ];

        $this->repository->expects($this->once())->method('addNameSort');
        $this->repository->expects($this->once())->method('addIdSort');

        $apiQuery->addSortCriteria($this->query, $this->repository, $sort);
    }

    public function testAddPageCriteria_WithPageSize_CallsPagination()
    {
        $apiQuery = $this->getMock('\ApiFoo\Api\ApiRepository', null, [$this->orm], '', true);

        $pageSize = 5;
        $pageNumber = 2;

        $result = $apiQuery->addPageCriteria($this->query, $this->repository, $pageSize, $pageNumber);
        $this->assertTrue($result);
    }

    public function testAddPageCriteria_WithPageSizeZero_DoesntCallPagination()
    {
        $apiQuery = $this->getMock('\ApiFoo\Api\ApiRepository', null, [$this->orm], '', true);

        $pageSize = 0;
        $pageNumber = 2;

        $result = $apiQuery->addPageCriteria($this->query, $this->repository, $pageSize, $pageNumber);
        $this->assertFalse($result);
    }

    public function testGetList_WithZeroCount_ReturnsEmptyData()
    {
        $apiQuery = $this->getMock('\ApiFoo\Api\ApiRepository', ['executeCountQuery'], [], '', false);
        $apiRequest = $this->getMock('\ApiFoo\Api\ApiRequest', null, [], '', false);

        $expected = [
            'data' => [],
            'meta' => ['count' => 0, 'pages' => 0],
        ];

        $apiQuery->method('executeCountQuery')->willReturn(0);
        $apiQuery->expects($this->once())->method('executeCountQuery');

        $actual = $apiQuery->getList($this->query, $apiRequest);
        $this->assertEquals($expected, $actual);
    }

    public function testGetList_WithNonZeroCount_ReturnsData()
    {
        $apiQuery = $this->getMock('\ApiFoo\Api\ApiRepository', ['executeCountQuery', 'executeIdsQuery', 'executeListQuery'], [], '', false);
        $apiRequest = $this->getMock('\ApiFoo\Api\ApiRequest', ['getPageSize'], [], '', false);

        $data = [
            ['id' => 1, 'name' => 'test1'],
            ['id' => 2, 'name' => 'test2'],
        ];

        $expected = [
            'data' => $data,
            'meta' => ['count' => 2, 'pages' => 1],
        ];

        $apiQuery->method('executeCountQuery')->willReturn(count($data));
        $apiQuery->method('executeIdsQuery')->willReturn([1,2]);
        $apiQuery->method('executeListQuery')->willReturn($data);
        $apiRequest->method('getPageSize')->willReturn(5);
        $apiQuery->expects($this->once())->method('executeCountQuery');
        $apiQuery->expects($this->once())->method('executeIdsQuery');
        $apiQuery->expects($this->once())->method('executeListQuery');

        $actual = $apiQuery->getList($this->query, $apiRequest);
        $this->assertEquals($expected, $actual);
    }

    public function testGetItem_WithMultipleResults_ReturnsEmptyData()
    {
        $apiQuery = $this->getMock('\ApiFoo\Api\ApiRepository', ['executeItemQuery'], [], '', false);
        $apiRequest = $this->getMock('\ApiFoo\Api\ApiRequest', null, [], '', false);

        $data = [
            ['id' => 1, 'name' => 'test1'],
            ['id' => 2, 'name' => 'test2'],
        ];

        $expected = [
            'data' => null,
            'meta' => [],
        ];

        $apiQuery->method('executeItemQuery')->willReturn($data);
        $apiQuery->expects($this->once())->method('executeItemQuery');

        $actual = $apiQuery->getItem($this->query, $apiRequest);
        $this->assertEquals($expected, $actual);
    }

    public function testGetItem_WithSingleResult_ReturnsData()
    {
        $apiQuery = $this->getMock('\ApiFoo\Api\ApiRepository', ['executeItemQuery'], [], '', false);
        $apiRequest = $this->getMock('\ApiFoo\Api\ApiRequest', null, [], '', false);

        $data = [
            ['id' => 1, 'name' => 'test1'],
        ];

        $expected = [
            'data' => $data[0],
            'meta' => [],
        ];

        $apiQuery->method('executeItemQuery')->willReturn($data);
        $apiQuery->expects($this->once())->method('executeItemQuery');

        $actual = $apiQuery->getItem($this->query, $apiRequest);
        $this->assertEquals($expected, $actual);
    }

    public function testExecuteCountQuery_CallsMethodsAndReturnsCount()
    {
        $apiQuery = $this->getMock('\ApiFoo\Api\ApiRepository', ['addFilterCriteria'], [$this->orm], '', true);
        $apiRequest = $this->getMock('\ApiFoo\Api\ApiRequest', ['getFilters'], [], '', false);

        $this->orm->method('executeQuery')->willReturn([['count' => 2]]);
        $apiRequest->method('getFilters')->willReturn([]);
        $apiQuery->expects($this->once())->method('addFilterCriteria');
        $this->orm->expects($this->once())->method('executeQuery');

        $count = $apiQuery->executeCountQuery($this->query, $apiRequest);
        $this->assertEquals(2, $count);
    }

    public function testExecuteIdsQuery_CallsMethodsAndReturnsIds()
    {
        $apiQuery = $this->getMock('\ApiFoo\Api\ApiRepository', ['addFilterCriteria', 'addSortCriteria', 'addPageCriteria'], [$this->orm], '', true);
        $apiRequest = $this->getMock('\ApiFoo\Api\ApiRequest', ['getFilters', 'getSort', 'getPageSize', 'getPageNumber'], [], '', false);

        $data = [
            ['id' => 1, 'name' => 'test1'],
            ['id' => 2, 'name' => 'test2'],
        ];

        $this->orm->method('executeQuery')->willReturn($data);
        $apiRequest->method('getFilters')->willReturn([]);
        $apiRequest->method('getSort')->willReturn([]);
        $apiRequest->method('getPageSize')->willReturn(10);
        $apiRequest->method('getPageNumber')->willReturn(1);
        $apiQuery->expects($this->once())->method('addFilterCriteria');
        $apiQuery->expects($this->once())->method('addSortCriteria');
        $apiQuery->expects($this->once())->method('addPageCriteria');
        $this->orm->expects($this->once())->method('executeQuery');
        
        $ids = $apiQuery->executeIdsQuery($this->query, $apiRequest);
        $this->assertEquals([1,2], $ids);
    }

    public function testExecuteListQuery_CallsMethodsAndReturnsResults()
    {
        $apiQuery = $this->getMock('\ApiFoo\Api\ApiRepository', ['addSortCriteria'], [$this->orm], '', true);
        $apiRequest = $this->getMock('\ApiFoo\Api\ApiRequest', ['getSort'], [], '', false);

        $data = [
            ['id' => 1, 'name' => 'test1'],
            ['id' => 2, 'name' => 'test2'],
        ];

        $this->orm->method('executeQuery')->willReturn($data);
        $apiRequest->method('getSort')->willReturn([]);
        $apiQuery->expects($this->once())->method('addSortCriteria');
        $this->orm->expects($this->once())->method('executeQuery');

        $results = $apiQuery->executeListQuery($this->query, $apiRequest, [1,2]);
        $this->assertEquals($data, $results);
    }

    public function testExecuteItemQuery_CallsMethodsAndReturnsResults()
    {
        $apiQuery = $this->getMock('\ApiFoo\Api\ApiRepository', ['addFilterCriteria'], [$this->orm], '', true);
        $apiRequest = $this->getMock('\ApiFoo\Api\ApiRequest', ['getFilters'], [], '', false);

        $data = [
            ['id' => 1, 'name' => 'test1'],
        ];

        $this->orm->method('executeQuery')->willReturn($data);
        $apiRequest->method('getFilters')->willReturn([]);
        $apiQuery->expects($this->once())->method('addFilterCriteria');
        $this->orm->expects($this->once())->method('executeQuery');

        $results = $apiQuery->executeItemQuery($this->query, $apiRequest);
        $this->assertEquals($data, $results);
    }
}