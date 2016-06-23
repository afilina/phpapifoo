<?php

use ApiFoo\Api\ApiRequest;

class ApiRequestTest extends \PHPUnit_Framework_TestCase
{
    private $request;

    public function setUp()
    {
        $this->request = $this->getMockBuilder('\Psr\Http\Message\ServerRequestInterface')
            ->getMock();
    }

    public function testConstructor_WithSortDescending_ExtractsOrder()
    {
        $this->request->method('getQueryParams')->willReturn([
            'sort' => '-title',
        ]);
        $apiRequest = new ApiRequest($this->request);
        $this->assertEquals(['title' => '-'], $apiRequest->getSort());
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructor_WithInvalidSort_ExtractsOrder()
    {
        $this->request->method('getQueryParams')->willReturn([
            'sort' => '*invalid*',
        ]);
        $apiRequest = new ApiRequest($this->request);
    }

    public function testConstructor_WithPageInfo_ExtractsPageInfo()
    {
        $this->request->method('getQueryParams')->willReturn([
            'pageSize' => 5,
            'pageNumber' => 2,
        ]);
        $apiRequest = new ApiRequest($this->request);
        $this->assertEquals(5, $apiRequest->getPageSize());
        $this->assertEquals(2, $apiRequest->getPageNumber());
    }

    public function testConstructor_WithUnmatchedString_ExtractsFilter()
    {
        $this->request->method('getQueryParams')->willReturn([
            'title' => 'value',
        ]);
        $apiRequest = new ApiRequest($this->request);
        $this->assertEquals(['title' => 'value'], $apiRequest->getFilters());
    }

    // validate filters?

    public function testAddSystemFilter()
    {
        $this->request->method('getQueryParams')->willReturn([]);
        $apiRequest = new ApiRequest($this->request);
        $apiRequest->addSystemFilter('title', 'value');
        $this->assertEquals(['title' => 'value'], $apiRequest->getFilters());
    }

    public function testAddUserFilter()
    {
        $this->request->method('getQueryParams')->willReturn([]);
        $apiRequest = new ApiRequest($this->request);
        $apiRequest->addUserFilter('title', 'value');
        $this->assertEquals(['title' => 'value'], $apiRequest->getFilters());
    }

    public function testGetBody_WithEmpty_ReturnsArray()
    {
        $this->request->method('getQueryParams')->willReturn([]);
        $this->request->method('getParsedBody')->willReturn('');
        $apiRequest = new ApiRequest($this->request);
        $this->assertEquals([], $apiRequest->getBody());
    }

    public function testGetBody_WithArray_ReturnsArray()
    {
        $this->request->method('getQueryParams')->willReturn([]);
        $this->request->method('getParsedBody')->willReturn(['title' => 'value']);
        $apiRequest = new ApiRequest($this->request);
        $this->assertEquals(['title' => 'value'], $apiRequest->getBody());
    }
}