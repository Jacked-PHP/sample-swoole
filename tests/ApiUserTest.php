<?php

namespace Tests;

use Nekofar\Slim\Test\Traits\AppTestTrait;

class ApiUserTest extends TestCase
{
    use AppTestTrait;

    protected function setUp(): void
    {
        $this->setUpApp($this->getApp());
    }

    public function test_can_get_users(): void
    {
        $response = $this->get('/api/users');
        $response->assertOk();

        $bodyStream = $response->getBody();
        $bodyStream->rewind();
        $data = json_decode($bodyStream->getContents(), true);

        $this->assertArrayHasKey('data', $data);

        $firstRecord = current($data['data']);
        $this->assertArrayHasKey('id', $firstRecord);
        $this->assertArrayHasKey('name', $firstRecord);
        $this->assertArrayHasKey('email', $firstRecord);
    }
}