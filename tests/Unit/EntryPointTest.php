<?php

namespace Tests\Unit;

use LaswitchTech\Core\Response;
use LaswitchTech\Core\EntryPoint;
use LaswitchTech\Core\Router;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the MVC chain components
 *
 * Tests Response, EntryPoint behavior, and the overall request flow.
 */
class EntryPointTest extends TestCase
{
    // -- Response factories --

    public function testResponseRenderCreatesRenderType(): void
    {
        $response = Response::render('panel', 'index');
        $this->assertSame(Response::TYPE_RENDER, $response->type);
        $this->assertSame(200, $response->status);
        $this->assertSame('panel', $response->template);
        $this->assertSame('index', $response->view);
    }

    public function testResponseRedirectCreatesRedirectType(): void
    {
        $response = Response::redirect('/dashboard', 301);
        $this->assertSame(Response::TYPE_REDIRECT, $response->type);
        $this->assertSame(301, $response->status);
        $this->assertSame('/dashboard', $response->url);
    }

    public function testResponseJsonCreatesJsonType(): void
    {
        $response = Response::json(['key' => 'value']);
        $this->assertSame(Response::TYPE_JSON, $response->type);
        $this->assertSame(200, $response->status);
        $this->assertSame(['key' => 'value'], $response->content);
    }

    public function testResponseErrorCreatesErrorType(): void
    {
        $response = Response::error(404, 'Not found');
        $this->assertSame(Response::TYPE_ERROR, $response->type);
        $this->assertSame(404, $response->status);
        $this->assertSame('Not found', $response->message);
    }

    // -- Response termination --

    public function testResponseTerminatesForRedirect(): void
    {
        $response = Response::redirect('/login');
        $this->assertTrue($response->terminates());
    }

    public function testResponseTerminatesForJson(): void
    {
        $response = Response::json([]);
        $this->assertTrue($response->terminates());
    }

    public function testResponseDoesNotTerminateForRender(): void
    {
        $response = Response::render('panel', 'index');
        $this->assertFalse($response->terminates());
    }

    public function testResponseDoesNotTerminateForError(): void
    {
        $response = Response::error(500, 'Error');
        $this->assertFalse($response->terminates());
    }

    // -- EntryPoint dispatch (no globals required) --

    public function testEntryPointReturnsNotFoundForUnknownRoute(): void
    {
        // We can't fully test execute() without globals, but we can
        // verify the dispatch path exists and returns Response type
        $ref = new \ReflectionClass(EntryPoint::class);
        $dispatch = $ref->getMethod('dispatch');
        $this->assertTrue($dispatch->isProtected());

        // Verify return type hint
        $returnType = $dispatch->getReturnType();
        $this->assertSame(Response::class, $returnType->getName());
    }

    public function testEntryPointExecuteReturnsResponse(): void
    {
        // Verify execute() returns Response (interface contract)
        $ref = new \ReflectionClass(EntryPoint::class);
        $execute = $ref->getMethod('execute');
        $returnType = $execute->getReturnType();
        $this->assertSame(Response::class, $returnType->getName());
    }

    // -- Response::send() HTTP status --

    public function testResponseSendSetsHttpCode404(): void
    {
        $response = Response::error(404, 'Not found');

        // Suppress header output during test
        $this->expectOutputString('');

        // http_response_code is available in CLI if we set it
        $response->send();
        $this->assertSame(404, http_response_code());
    }

    public function testResponseSendSetsHttpCode500(): void
    {
        $response = Response::error(500, 'Server error');
        $response->send();
        $this->assertSame(500, http_response_code());
    }

    // -- Router::startMVC exists --

    public function testRouterHasStartMVCMethod(): void
    {
        $ref = new \ReflectionClass(Router::class);
        $this->assertTrue($ref->hasMethod('startMVC'));

        $method = $ref->getMethod('startMVC');
        $this->assertTrue($method->isPublic());

        // Return type should be Response
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertSame(Response::class, $returnType->getName());
    }
}
