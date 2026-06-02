<?php

namespace Tests\Unit;

use LaswitchTech\Core\View;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    public function testViewClassExists(): void
    {
        $this->assertTrue(class_exists(View::class));
    }

    public function testViewHasResolveMethods(): void
    {
        $view = new View();
        $this->assertTrue(method_exists($view, 'render'));
        $this->assertTrue(method_exists($view, 'view'));
        $this->assertTrue(method_exists($view, 'resolveTemplate'));
        $this->assertTrue(method_exists($view, 'resolveView'));
    }

    public function testViewConstructorInstantiable(): void
    {
        $view = new View();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testViewResolveMethodsReturnStrings(): void
    {
        // Since Config::root() uses getcwd() or $_SERVER['DOCUMENT_ROOT'],
        // resolve methods return paths relative to the current working directory.
        // They may not find files, but they return string paths.
        $view = new View();

        $template = $view->resolveTemplate('test');
        $this->assertIsString($template);
        $this->assertStringContainsString('test', $template);

        $viewPath = $view->resolveView('test');
        $this->assertIsString($viewPath);
        $this->assertStringContainsString('test', $viewPath);
    }
}
