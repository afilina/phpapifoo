<?php
namespace ApiFoo\Api;

use Psr\Http\Message\ServerRequestInterface;

class ApiRequest
{
    private $userFilters = [];
    private $systemFilters = [];
    private $sort = [];
    private $pageSize = 10;
    private $pageNumber = 1;
    private $body = 1;

    public function getFilters()
    {
        return $this->userFilters + $this->systemFilters;
    }

    public function addSystemFilter($filter, $value)
    {
        $this->systemFilters[$filter] = $value;
    }

    public function addUserFilter($filter, $value)
    {
        $this->userFilters[$filter] = $value;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function getPageSize()
    {
        return $this->pageSize;
    }

    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    public function getBody()
    {
        if ($this->body == null) {
            return [];
        }
        return $this->body;
    }

    public function __construct(ServerRequestInterface $request)
    {
        $this->body = $request->getParsedBody();
        foreach ($request->getQueryParams() as $param => $value) {
            switch ($param) {
                case 'sort':
                    $parts = explode(',', $value);
                    foreach ($parts as $part) {
                        preg_match_all('/^(\-)?([\w]+)$/', $part, $matches);
                        if (empty($matches[0])) {
                            throw new \Exception('Invalid sort format.', 1);
                        }
                        $name = $matches[2][0];
                        $order = $matches[1][0];
                        $this->sort[$name] = $order;
                    }
                    break;
                case 'pageSize':
                case 'pageNumber':
                    $this->{$param} = $value;
                    break;
                default:
                    $this->userFilters[$param] = $value;
            }
        }
    }
}