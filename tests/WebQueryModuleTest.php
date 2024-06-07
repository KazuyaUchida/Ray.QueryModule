<?php

declare(strict_types=1);

namespace Ray\Query;

use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

use function assert;
use function is_array;

class WebQueryModuleTest extends TestCase
{
    /** @var AbstractModule */
    private $module;

    protected function setUp(): void
    {
        $webQueryConfig = [
            'foo' => ['GET', 'https://httpbin.org/anything/foo'],
            'bar' => ['GET', 'https://httpbin.org/anything/bar'],
        ];
        $guzzleConfig = [];
        $this->module = new WebQueryModule($webQueryConfig, $guzzleConfig);
    }

    public function testQueryInterface(): void
    {
        $foo = (new Injector($this->module, __DIR__ . '/tmp'))->getInstance(QueryInterface::class, 'foo');
        $this->assertInstanceOf(QueryInterface::class, $foo);
        $result = $foo([]);
        assert(is_array($result));
        assert(isset($result['url']));
        $this->assertSame('https://httpbin.org/anything/foo', $result['url']);
    }

    public function testCallable(): void
    {
        $foo = (new Injector($this->module, __DIR__ . '/tmp'))->getInstance('', 'foo');
        $this->assertIsCallable($foo);
        $result = $foo([]);
        $this->assertSame('https://httpbin.org/anything/foo', $result['url']);
    }
}
