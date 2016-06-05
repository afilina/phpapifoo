<?php

use ApiFoo\Adapters\Request\Cake\CakeRequest32;
use Cake\Network\Request;

/**
 * Integration tests for Cake HTTP Request version 3.2
 */
class CakeRequest32Test extends \PHPUnit_Framework_TestCase
{
    public function testGetQueryParams_WithFilters_ReturnsFilters()
    {
        $request = new Request();
        $request->query = ['name' => 'test'];
        $requestWrapper = $this->getMock('\ApiFoo\Adapters\Request\Cake\CakeRequest32', null, [$request]);
        $params = $requestWrapper->getQueryParams();
        $this->assertEquals(['name' => 'test'], $params);
    }

    public function testGetParsedBody_WithJson_ReturnsArray()
    {
        $request = new Request();
        $request->data = ['name' => 'test'];
        $requestWrapper = $this->getMock('\ApiFoo\Adapters\Request\Cake\CakeRequest32', null, [$request]);
        $params = $requestWrapper->getParsedBody();
        $this->assertEquals(['name' => 'test'], $params);
    }

    public function testGetUploadedFiles_WithFile_ReturnsArray()
    {
        $request = new Request();
        $request->files = ['file' => ['tmp_name' => 'file.txt']];
        $requestWrapper = $this->getMock('\ApiFoo\Adapters\Request\Cake\CakeRequest32', null, [$request]);
        $params = $requestWrapper->getUploadedFiles();
        $this->assertEquals(['file' => ['tmp_name' => 'file.txt']], $params);
    }
}