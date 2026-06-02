<?php

namespace Tests\Unit;

use LaswitchTech\Core\Router;
use LaswitchTech\Core\Objects\RouteDTO;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function testRouterClassExists(): void
    {
        $this->assertTrue(class_exists(Router::class));
    }

    public function testRouterHasRegisterMethod(): void
    {
        $this->assertTrue(method_exists(Router::class, 'register'));
    }

    public function testRouterHasMatchMethod(): void
    {
        $this->assertTrue(method_exists(Router::class, 'match'));
    }

    public function testRouterHasAllMethod(): void
    {
        $this->assertTrue(method_exists(Router::class, 'all'));
    }

    public function testRouterHasLoadFromConfigMethod(): void
    {
        $this->assertTrue(method_exists(Router::class, 'loadFromConfig'));
    }

    public function testRouterHasRouteMethod(): void
    {
        $this->assertTrue(method_exists(Router::class, 'route'));
    }

    public function testRouterHasRoutesMethod(): void
    {
        $this->assertTrue(method_exists(Router::class, 'routes'));
    }

    public function testRouterHasSetMethod(): void
    {
        $this->assertTrue(method_exists(Router::class, 'set'));
    }

    public function testRouterHasRenderMethod(): void
    {
        $this->assertTrue(method_exists(Router::class, 'render'));
    }

    public function testRouterHasStartMethod(): void
    {
        $this->assertTrue(method_exists(Router::class, 'start'));
    }

    public function testRouterHasModulesConstant(): void
    {
        $this->assertIsArray(Router::Modules);
        $this->assertContains('css', Router::Modules);
        $this->assertContains('logo', Router::Modules);
    }

    public function testRouterHasHttpCodesConstant(): void
    {
        $this->assertIsArray(Router::HttpCodes);
        $this->assertContains(404, Router::HttpCodes);
        $this->assertContains(500, Router::HttpCodes);
    }

    public function testRouteDTOCanBeCreated(): void
    {
        $dto = new RouteDTO('/test', [
            'template' => 'panel.php',
            'view' => 'index.php',
            'public' => true,
            'level' => 0,
            'action' => 'test/fetch',
            'label' => 'Test',
        ]);

        $this->assertEquals('/test', $dto->namespace);
        $this->assertEquals('panel.php', $dto->template);
        $this->assertTrue($dto->public);
        $this->assertEquals('test/fetch', $dto->action);
    }

    public function testResponseClassHasAllMethods(): void
    {
        $this->assertTrue(method_exists(\LaswitchTech\Core\Response::class, 'render'));
        $this->assertTrue(method_exists(\LaswitchTech\Core\Response::class, 'redirect'));
        $this->assertTrue(method_exists(\LaswitchTech\Core\Response::class, 'json'));
        $this->assertTrue(method_exists(\LaswitchTech\Core\Response::class, 'error'));
    }

    public function testResponseHasConstants(): void
    {
        $this->assertEquals('render', \LaswitchTech\Core\Response::TYPE_RENDER);
        $this->assertEquals('redirect', \LaswitchTech\Core\Response::TYPE_REDIRECT);
        $this->assertEquals('json', \LaswitchTech\Core\Response::TYPE_JSON);
        $this->assertEquals('error', \LaswitchTech\Core\Response::TYPE_ERROR);
    }

    public function testResponseRender(): void
    {
        $response = \LaswitchTech\Core\Response::render('panel', 'index');
        $this->assertEquals('render', $response->type);
        $this->assertEquals('panel', $response->template);
        $this->assertEquals('index', $response->view);
    }

    public function testResponseRedirect(): void
    {
        $response = \LaswitchTech\Core\Response::redirect('/dashboard');
        $this->assertEquals('redirect', $response->type);
        $this->assertEquals('/dashboard', $response->url);
        $this->assertEquals(302, $response->status);
        $this->assertTrue($response->terminates());
    }

    public function testResponseJson(): void
    {
        $response = \LaswitchTech\Core\Response::json(['key' => 'value']);
        $this->assertEquals('json', $response->type);
        $this->assertEquals(['key' => 'value'], $response->content);
        $this->assertTrue($response->terminates());
    }

    public function testResponseError(): void
    {
        $response = \LaswitchTech\Core\Response::error(404, 'Not found');
        $this->assertEquals('error', $response->type);
        $this->assertEquals(404, $response->status);
        $this->assertEquals('Not found', $response->message);
    }

    public function testResponseTerminates(): void
    {
        $this->assertTrue(\LaswitchTech\Core\Response::redirect('/x')->terminates());
        $this->assertTrue(\LaswitchTech\Core\Response::json([])->terminates());
        $this->assertFalse(\LaswitchTech\Core\Response::render('x', 'y')->terminates());
        $this->assertFalse(\LaswitchTech\Core\Response::error(404)->terminates());
    }
}
