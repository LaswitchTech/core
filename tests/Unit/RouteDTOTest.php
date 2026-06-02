<?php

namespace Tests\Unit;

use LaswitchTech\Core\Objects\RouteDTO;
use PHPUnit\Framework\TestCase;

class RouteDTOTest extends TestCase
{
    /** @var RouteDTO */
    protected $dto;

    protected function setUp(): void
    {
        $this->dto = new RouteDTO('/dashboard', [
            'template' => 'panel.php',
            'view' => 'index.php',
            'public' => false,
            'level' => 1,
            'action' => 'dashboard/fetch',
            'parent' => null,
            'location' => ['apps'],
            'label' => 'Dashboard',
            'icon' => 'speedometer2',
            'color' => null,
        ]);
    }

    public function testNamespace(): void
    {
        $this->assertEquals('/dashboard', $this->dto->namespace);
    }

    public function testTemplate(): void
    {
        $this->assertEquals('panel.php', $this->dto->template);
    }

    public function testView(): void
    {
        $this->assertEquals('index.php', $this->dto->view);
    }

    public function testPublic(): void
    {
        $this->assertFalse($this->dto->public);
    }

    public function testLevel(): void
    {
        $this->assertEquals(1, $this->dto->level);
    }

    public function testAction(): void
    {
        $this->assertEquals('dashboard/fetch', $this->dto->action);
    }

    public function testParent(): void
    {
        $this->assertNull($this->dto->parent);
    }

    public function testLocation(): void
    {
        $this->assertEquals(['apps'], $this->dto->location);
    }

    public function testLabel(): void
    {
        $this->assertEquals('Dashboard', $this->dto->label);
    }

    public function testIcon(): void
    {
        $this->assertEquals('speedometer2', $this->dto->icon);
    }

    public function testColor(): void
    {
        $this->assertNull($this->dto->color);
    }

    public function testToArray(): void
    {
        $array = $this->dto->toArray();
        $this->assertArrayHasKey('template', $array);
        $this->assertArrayHasKey('view', $array);
        $this->assertArrayHasKey('public', $array);
        $this->assertArrayHasKey('level', $array);
        $this->assertArrayHasKey('action', $array);
        $this->assertArrayHasKey('location', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('icon', $array);
        $this->assertArrayHasKey('color', $array);
        $this->assertArrayHasKey('parent', $array);
    }

    public function testIsPrivate(): void
    {
        $this->assertTrue($this->dto->isPrivate());
    }

    public function testIsPrivate_false(): void
    {
        $public = new RouteDTO('/public', ['public' => true]);
        $this->assertFalse($public->isPrivate());
    }

    public function testMinimal(): void
    {
        $dto = new RouteDTO('/minimal');
        $this->assertEquals('/minimal', $dto->namespace);
        $this->assertNull($dto->template);
        $this->assertNull($dto->view);
        $this->assertTrue($dto->public);
        $this->assertEquals(0, $dto->level);
        $this->assertNull($dto->action);
    }
}
