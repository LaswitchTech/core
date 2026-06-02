<?php

namespace Tests\Unit;

use LaswitchTech\Core\Controller;
use LaswitchTech\Core\Response;
use ReflectionClass;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    public function testControllerClassExists(): void
    {
        $this->assertTrue(class_exists(Controller::class));
    }

    public function testControllerExtendsBaseController(): void
    {
        $ref = new ReflectionClass(Controller::class);
        $this->assertTrue($ref->getParentClass() !== false);
        $this->assertEquals('LaswitchTech\Core\Abstracts\Controller', $ref->getParentClass()->name);
    }

    public function testControllerHasResponseMethod(): void
    {
        $this->assertTrue(method_exists(Controller::class, 'Response'));
    }

    public function testControllerHasGetViewMethod(): void
    {
        $this->assertTrue(method_exists(Controller::class, 'getView'));
    }

    public function testControllerHasDefaultAction(): void
    {
        $this->assertTrue(method_exists(Controller::class, 'defaultAction'));
    }

    public function testControllerHasSendResponseMethod(): void
    {
        $this->assertTrue(method_exists(Controller::class, 'sendResponse'));
    }

    public function testResponseStaticMethods(): void
    {
        $this->assertTrue(method_exists(Response::class, 'render'));
        $this->assertTrue(method_exists(Response::class, 'redirect'));
        $this->assertTrue(method_exists(Response::class, 'json'));
        $this->assertTrue(method_exists(Response::class, 'error'));
    }

    public function testResponseConstants(): void
    {
        $this->assertEquals('render', Response::TYPE_RENDER);
        $this->assertEquals('redirect', Response::TYPE_REDIRECT);
        $this->assertEquals('json', Response::TYPE_JSON);
        $this->assertEquals('error', Response::TYPE_ERROR);
    }
}
