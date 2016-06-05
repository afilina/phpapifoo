<?php
namespace ApiFoo\Adapters\Request\Cake;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;

class CakeRequest32 implements ServerRequestInterface
{
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function getServerParams()
    {
        throw new \Exception('Not implemented');
    }

    public function getCookieParams()
    {
        throw new \Exception('Not implemented');
    }

    public function withCookieParams(array $cookies)
    {
        throw new \Exception('Not implemented');
    }

    public function getQueryParams()
    {
        return $this->request->query;
    }

    public function withQueryParams(array $query)
    {
        throw new \Exception('Not implemented');
    }

    public function getUploadedFiles()
    {
        return $this->request->files;
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        throw new \Exception('Not implemented');
    }

    public function getParsedBody()
    {
        return $this->request->data;
    }

    public function withParsedBody($data)
    {
        throw new \Exception('Not implemented');
    }

    public function getAttributes()
    {
        throw new \Exception('Not implemented');
    }

    public function getAttribute($name, $default = NULL)
    {
        throw new \Exception('Not implemented');
    }

    public function withAttribute($name, $value)
    {
        throw new \Exception('Not implemented');
    }

    public function withoutAttribute($name)
    {
        throw new \Exception('Not implemented');
    }

    public function getRequestTarget()
    {
        throw new \Exception('Not implemented');
    }

    public function withRequestTarget($requestTarget)
    {
        throw new \Exception('Not implemented');
    }

    public function getMethod()
    {
        throw new \Exception('Not implemented');
    }

    public function withMethod($method)
    {
        throw new \Exception('Not implemented');
    }

    public function getUri()
    {
        throw new \Exception('Not implemented');
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        throw new \Exception('Not implemented');
    }

    public function getProtocolVersion()
    {
        throw new \Exception('Not implemented');
    }

    public function withProtocolVersion($version)
    {
        throw new \Exception('Not implemented');
    }

    public function getHeaders()
    {
        throw new \Exception('Not implemented');
    }

    public function hasHeader($name)
    {
        throw new \Exception('Not implemented');
    }

    public function getHeader($name)
    {
        throw new \Exception('Not implemented');
    }

    public function getHeaderLine($name)
    {
        throw new \Exception('Not implemented');
    }

    public function withHeader($name, $value)
    {
        throw new \Exception('Not implemented');
    }

    public function withAddedHeader($name, $value)
    {
        throw new \Exception('Not implemented');
    }

    public function withoutHeader($name)
    {
        throw new \Exception('Not implemented');
    }

    public function getBody()
    {
        throw new \Exception('Not implemented');
    }

    public function withBody(StreamInterface $body)
    {
        throw new \Exception('Not implemented');
    }
}