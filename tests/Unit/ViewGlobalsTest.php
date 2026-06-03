<?php

namespace Tests\Unit;

use LaswitchTech\Core\ViewGlobals;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Global View Context system
 */
class ViewGlobalsTest extends TestCase
{
    public function testViewGlobalsClassExists(): void
    {
        $this->assertTrue(class_exists(ViewGlobals::class));
    }

    public function testViewGlobalsHasApplyMethod(): void
    {
        $ref = new \ReflectionClass(ViewGlobals::class);
        $this->assertTrue($ref->hasMethod('apply'));
        $method = $ref->getMethod('apply');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
    }

    public function testViewGlobalsHasContextMethod(): void
    {
        $ref = new \ReflectionClass(ViewGlobals::class);
        $this->assertTrue($ref->hasMethod('context'));
    }

    public function testViewGlobalsDefinesGlobals(): void
    {
        $ref = new \ReflectionClass(ViewGlobals::class);
        $consts = $ref->getConstants();
        $this->assertArrayHasKey('NAMES', $consts);
        $this->assertContains('config', $consts['NAMES']);
        $this->assertContains('auth', $consts['NAMES']);
        $this->assertContains('currentUser', $consts['NAMES']);
        $this->assertContains('menu', $consts['NAMES']);
        $this->assertContains('breadcrumbs', $consts['NAMES']);
        $this->assertContains('locale', $consts['NAMES']);
        $this->assertContains('csrf', $consts['NAMES']);
        $this->assertContains('request', $consts['NAMES']);
        $this->assertContains('output', $consts['NAMES']);
        $this->assertContains('app', $consts['NAMES']);
    }

    public function testViewGlobalsApplyDoesNotThrow(): void
    {
        // ViewGlobals::apply() should not throw even with no globals
        $this->expectNotToPerformAssertions();
        ViewGlobals::apply();
    }

    public function testViewGlobalsContextReturnsArray(): void
    {
        $context = ViewGlobals::context();
        $this->assertIsArray($context);
        $this->assertCount(10, $context);
    }
}
