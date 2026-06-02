<?php

namespace Tests\Unit;

use LaswitchTech\Core\Middleware\MiddlewareInterface;
use LaswitchTech\Core\Objects\RouteDTO;
use LaswitchTech\Core;
use ReflectionClass;
use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    public function testMiddlewareInterfaceExists(): void
    {
        $this->assertTrue(class_exists(MiddlewareInterface::class));
    }

    public function testMiddlewareInterfaceHasHandleMethod(): void
    {
        $ref = new ReflectionClass(MiddlewareInterface::class);
        $this->assertTrue($ref->hasMethod('handle'));
    }

    public function testAuthMiddlewareClassExists(): void
    {
        $this->assertTrue(class_exists('\LaswitchTech\Core\Middleware\AuthMiddleware'));
    }

    public function testMaintenanceMiddlewareClassExists(): void
    {
        $this->assertTrue(class_exists('\LaswitchTech\Core\Middleware\MaintenanceMiddleware'));
    }

    public function testHookClassExists(): void
    {
        $this->assertTrue(class_exists('\LaswitchTech\Core\Hook'));
    }

    public function testHookHasRegisterAndFire(): void
    {
        $ref = new ReflectionClass('\LaswitchTech\Core\Hook');
        $this->assertTrue($ref->hasMethod('register'));
        $this->assertTrue($ref->hasMethod('fire'));
    }

    public function testHookDefaults(): void
    {
        $defaults = \LaswitchTech\Core\Hook::defaults();
        $this->assertContains('route.registered', $defaults);
        $this->assertContains('auth.fail', $defaults);
        $this->assertContains('view.before', $defaults);
        $this->assertContains('view.after', $defaults);
    }

    public function testRouteDTOHasAllProperties(): void
    {
        $ref = new ReflectionClass(RouteDTO::class);
        $props = ['namespace', 'template', 'view', 'public', 'level', 'action', 'parent', 'location', 'label', 'icon', 'color'];
        foreach ($props as $prop) {
            $this->assertTrue($ref->hasProperty($prop));
        }
    }

    public function testHookCanRegisterAndFire(): void
    {
        $called = false;
        \LaswitchTech\Core\Hook::register('test.hook', function($arg) use (&$called) {
            $called = ($arg === 'hello');
            return $arg;
        });

        $result = \LaswitchTech\Core\Hook::fire('test.hook', 'hello');
        $this->assertTrue($called);
        $this->assertEquals(['hello'], $result);

        // Cleanup
        unset($GLOBALS['_HOOKS']['test.hook']);
    }

    public function testHookHasListeners(): void
    {
        \LaswitchTech\Core\Hook::register('test.listeners', function() {});
        $this->assertTrue(\LaswitchTech\Core\Hook::hasListeners('test.listeners'));
        $this->assertFalse(\LaswitchTech\Core\Hook::hasListeners('nonexistent.hook'));

        // Cleanup
        unset($GLOBALS['_HOOKS']['test.listeners']);
    }

    public function testHookNames(): void
    {
        \LaswitchTech\Core\Hook::register('test.names', function() {});
        $names = \LaswitchTech\Core\Hook::names();
        $this->assertContains('test.names', $names);

        // Cleanup
        unset($GLOBALS['_HOOKS']['test.names']);
    }
}
