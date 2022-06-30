<?php

namespace Tests\Feature;

use Nekofar\Slim\Test\TestResponse;
use Nekofar\Slim\Test\Traits\AppTestTrait;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use AppTestTrait;

    protected array $cookies = [];

    protected function setUp(): void
    {
        $this->setUpApp($this->getApp());
    }

    private function successfullLogin(): TestResponse
    {
        return $this->post('/login', [
            'email' => 'savio@example.com',
            'password' => 'secret',
        ]);
    }

    /**
     * Overwrite send method to add cookies from request header.
     *
     * @param RequestInterface $request
     * @param array $headers
     * @return TestResponse
     */
    private function send(RequestInterface $request, array $headers): TestResponse
    {
        if (!empty($this->cookies)) {
            $request = $request->withCookieParams($this->cookies);
        }

        if (null !== $this->defaultHeaders) {
            $headers = array_merge($this->defaultHeaders, $headers);
        }

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        /* @phpstan-ignore-next-line */
        return TestResponse::fromBaseResponse($this->app->handle($request));
    }

    public function test_can_login(): void
    {
        $response = $this->successfullLogin();
        $response->assertStatus(302);
        $response->assertHeader('Location', '/admin');

        $session = $this->getSessionCookieFromResponse($response);
        $this->assertArrayHasKey('user_id', $session);
    }

    public function test_cant_login_with_wrong_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'savio@example.com',
            'password' => 'wrongpass',
        ]);
        $response->assertStatus(302);

        $redirectUrl = current($response->getHeader('Location'));
        $parsedUrl = parse_url($redirectUrl);
        parse_str($parsedUrl['query'], $parsedUrl['query']);

        $this->assertEquals('/login', $parsedUrl['path']);
        $this->assertEquals('Failed to authenticate!', $parsedUrl['query']['error']);

        $session = $this->getSessionCookieFromResponse($response);
        $this->assertArrayNotHasKey('user_id', $session);
    }

    public function test_cant_login_with_not_existent_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpass',
        ]);
        $response->assertStatus(302);

        $redirectUrl = current($response->getHeader('Location'));
        $parsedUrl = parse_url($redirectUrl);
        parse_str($parsedUrl['query'], $parsedUrl['query']);

        $this->assertEquals('/login', $parsedUrl['path']);
        $this->assertEquals('Failed to authenticate!', $parsedUrl['query']['error']);

        $session = $this->getSessionCookieFromResponse($response);
        $this->assertArrayNotHasKey('user_id', $session);
    }

    public function test_can_logout(): void
    {
        $cookieKey = 'Set-Cookie';

        // login
        $response = $this->successfullLogin();
        $response->assertStatus(302);
        $session = $this->getSessionCookieFromResponse($response);
        $this->assertArrayHasKey('user_id', $session);

        $this->cookies = $this->getCookieParams($response);

        // access and verify if session is with cookie
        $response = $this->get('/admin');
        $response->assertOk();
        $session = $this->getSessionCookieFromResponse($response);
        $this->assertArrayHasKey('user_id', $session);

        // logout
        $response = $this->post('/logout', []);
        $session = $this->getSessionCookieFromResponse($response);
        $this->assertArrayNotHasKey('user_id', $session);
    }
}