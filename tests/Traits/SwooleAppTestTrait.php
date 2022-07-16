<?php

namespace Tests\Traits;

use Fig\Http\Message\RequestMethodInterface;
use Nekofar\Slim\Test\TestResponse;
use Nekofar\Slim\Test\Traits\AppTestTrait;

trait SwooleAppTestTrait
{
    use AppTestTrait;

    public function sPostJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $request = $this->createJsonRequest(RequestMethodInterface::METHOD_POST, $uri, $data);

        $request->getBody()->rewind();

        return $this->send($request, $headers);
    }

    public function sPutJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $request = $this->createJsonRequest(RequestMethodInterface::METHOD_PUT, $uri, $data);

        $request->getBody()->rewind();

        return $this->send($request, $headers);
    }
}