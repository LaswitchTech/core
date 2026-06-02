<?php

namespace Tests\Unit;

use LaswitchTech\Core\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testRender(): void
    {
        $response = Response::render('panel', 'index');
        $this->assertEquals(Response::TYPE_RENDER, $response->type);
        $this->assertEquals(200, $response->status);
        $this->assertEquals('panel', $response->template);
        $this->assertEquals('index', $response->view);
        $this->assertNull($response->content);
        $this->assertFalse($response->terminates());
    }

    public function testRedirect(): void
    {
        $response = Response::redirect('/dashboard', 301);
        $this->assertEquals(Response::TYPE_REDIRECT, $response->type);
        $this->assertEquals(301, $response->status);
        $this->assertEquals('/dashboard', $response->url);
        $this->assertTrue($response->terminates());
    }

    public function testJson(): void
    {
        $response = Response::json(['status' => 'ok'], 200, []);
        $this->assertEquals(Response::TYPE_JSON, $response->type);
        $this->assertEquals(200, $response->status);
        $this->assertEquals(['status' => 'ok'], $response->content);
        $this->assertEquals(['Content-Type: application/json; charset=utf-8'], $response->headers);
        $this->assertTrue($response->terminates());
    }

    public function testJsonContentType(): void
    {
        $response = Response::json(['key' => 'value']);
        $this->assertContains('Content-Type: application/json; charset=utf-8', $response->headers);
    }

    public function testError(): void
    {
        $response = Response::error(404, 'Not found');
        $this->assertEquals(Response::TYPE_ERROR, $response->type);
        $this->assertEquals(404, $response->status);
        $this->assertEquals('Not found', $response->message);
        $this->assertFalse($response->terminates());
    }

    public function testErrorWithTemplate(): void
    {
        $response = Response::error(500, 'Internal error', 'error', '500');
        $this->assertEquals(500, $response->status);
        $this->assertEquals('Internal error', $response->message);
        $this->assertEquals('error', $response->template);
        $this->assertEquals('500', $response->view);
    }

    public function testRedirectDefaultStatus(): void
    {
        $response = Response::redirect('/login');
        $this->assertEquals(302, $response->status);
    }

    public function testRenderDefaultStatus(): void
    {
        $response = Response::render('panel', 'index');
        $this->assertEquals(200, $response->status);
    }

    public function testRenderDefaultData(): void
    {
        $response = Response::render('panel', 'index');
        $this->assertEquals([], $response->data);
    }
}
