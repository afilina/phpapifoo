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
        $this->orm = $this->getMockBuilder('\ApiFoo\Interfaces\OrmInterface')
            ->getMock();

        $this->repository = $this->getMockBuilder('Table')
            ->setMethods(['addNameFilter', 'addIdFilter', 'addNameSort', 'addIdSort'])
            ->setMockClassName('TestRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->query = $this->getMockBuilder('Query')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testAddFilterCriteria_WithFilters_CallsFilterMethods()
    {
        $apiQuery = $this->getMockBuilder('\ApiFoo\Api\ApiRepository')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

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
        $apiQuery = $this->getMockBuilder('\ApiFoo\Api\ApiRepository')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $filters = [
            'id' => '',
        ];

        $this->repository->expects($this->never())->method('addIdFilter');

        $apiQuery->addFilterCriteria($this->query, $this->repository, $filters);
    }

    public function testAddSortCriteria_WithSorts_CallsSortMethods()
    {
        $apiQuery = $this->getMockBuilder('\ApiFoo\Api\ApiRepository')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

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
        $apiQuery = $this->getMockBuilder('\ApiFoo\Api\ApiRepository')
            ->setMethods(null)
            ->setConstructorArgs([$this->orm])
            ->getMock();

        $pageSize = 5;
        $pageNumber = 2;

        $result = $apiQuery->addPageCriteria($this->query, $this->repository, $pageSize, $pageNumber);
        $this->assertTrue($result);
    }

    public function testAddPageCriteria_WithPageSizeZero_DoesntCallPagination()
    {
        $apiQuery = $this->getMockBuilder('\ApiFoo\Api\ApiRepository')
            ->setMethods(null)
            ->setConstructorArgs([$this->orm])
            ->getMock();

        $pageSize = 0;
        $pageNumber = 2;

        $result = $apiQuery->addPageCriteria($this->query, $this->repository, $pageSize, $pageNumber);
        $this->assertFalse($result);
    }

    public function testGetList_WithZeroCount_ReturnsEmptyData()
    {
        $apiQuery = $this->getMockBuilder('\ApiFoo\Api\ApiRepository')
            ->setMethods(['executeCountQuery'])
            ->disableOriginalConstructor()
            ->getMock();

        $apiRequest = $this->getMockBuilder('\ApiFoo\Api\ApiRequest')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

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
        $apiQuery = $this->getMockBuilder('\ApiFoo\Api\ApiRepository')
            ->setMethods(['executeCountQuery', 'executeIdsQuery', 'executeListQuery'])
            ->disableOriginalConstructor()
            ->getMock();

        $apiRequest = $this->getMockBuilder('\ApiFoo\Api\ApiRequest')
            ->setMethods(['getPageSize'])
            ->disableOriginalConstructor()
            ->getMock();

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
        $apiQuery = $this->getMockBuilder('\ApiFoo\Api\ApiRepository')
            ->setMethods(['executeItemQuery'])
            ->disableOriginalConstructor()
            ->getMock();

        $apiRequest = $this->getMockBuilder('\ApiFoo\Api\ApiRequest')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

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
        $apiQuery = $this->getMockBuilder('\ApiFoo\Api\ApiRepository')
            ->setMethods(['executeItemQuery'])
            ->disableOriginalConstructor()
            ->getMock();

        $apiRequest = $this->getMockBuilder('\ApiFoo\Api\ApiRequest')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

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
        $apiQuery = $this->getMockBuilder('\ApiFoo\Api\ApiRepository')
            ->setMethods(['addFilterCriteria'])
            ->setConstructorArgs([$this->orm])
            ->getMock();

        $apiRequest = $this->getMockBuilder('\ApiFoo\Api\ApiRequest')
            ->setMethods(['getFilters'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->orm->method('executeQuery')->willReturn([['count' => 2]]);
        $apiRequest->method('getFilters')->willReturn([]);
        $apiQuery->expects($this->once())->method('addFilterCriteria');
        $this->orm->expects($this->once())->method('executeQuery');

        $count = $apiQuery->executeCountQuery($this->query, $apiRequest);
        $this->assertEquals(2, $count);
    }

    public function testExecuteIdsQuery_CallsMethodsAndReturnsIds()
    {
        $apiQuery = $this->getMockBuilder('\ApiFoo\Api\ApiRepository')
            ->setMethods(['addFilterCriteria', 'addSortCriteria', 'addPageCriteria'])
            ->setConstructorArgs([$this->orm])
            ->getMock();

        $apiRequest = $this->getMockBuilder('\ApiFoo\Api\ApiRequest')
            ->setMethods(['getFilters', 'getSort', 'getPageSize', 'getPageNumber'])
            ->disableOriginalConstructor()
            ->getMock();

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
        $apiQuery = $this->getMockBuilder('\ApiFoo\Api\ApiRepository')
            ->setMethods(['addSortCriteria'])
            ->setConstructorArgs([$this->orm])
            ->getMock();

        $apiRequest = $this->getMockBuilder('\ApiFoo\Api\ApiRequest')
            ->setMethods(['getSort'])
            ->disableOriginalConstructor()
            ->getMock();

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
        $apiQuery = $this->getMockBuilder('\ApiFoo\Api\ApiRepository')
            ->setMethods(['addFilterCriteria'])
            ->setConstructorArgs([$this->orm])
            ->getMock();

        $apiRequest = $this->getMockBuilder('\ApiFoo\Api\ApiRequest')
            ->setMethods(['getFilters'])
            ->disableOriginalConstructor()
            ->getMock();

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