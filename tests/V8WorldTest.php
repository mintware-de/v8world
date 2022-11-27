<?php

declare(strict_types=1);

namespace MintwareDe\Tests\V8World;

use MintwareDe\V8World\V8World;
use PHPUnit\Framework\TestCase;

class V8WorldTest extends TestCase
{
    private \V8Js $mockV8Js;
    /** @var V8World<\stdClass> */
    private V8World $world;

    protected function setUp(): void
    {
        $this->mockV8Js = new \V8Js();
        $this->world = V8World::create($this->mockV8Js, \stdClass::class);
    }

    public function testGetInstance(): void
    {
        self::assertEquals($this->mockV8Js, $this->world->getInstance());
    }

    public function testExecuteString_Pass(): void
    {
        $result = $this->world->executeString('1+1');
        self::assertEquals(2, $result);
    }

    public function testMissingMethod_Fail(): void
    {
        $newWorld = $this->world->modify('', newState: GreetingStubInterface::class);
        self::expectException(\V8JsScriptException::class);
        $newWorld->hello('test');
    }

    public function testCall_Pass(): void
    {
        $greetingWorld = $this->world->modify('hello = (n) => `Hello ${n}!`', newState: GreetingStubInterface::class);
        self::assertEquals('Hello Bob!', $greetingWorld->hello('Bob'));
    }

    public function testGetProperty_Pass(): void
    {
        $multiLangGreetingWorld = $this->world->modify(
            'language = \'fr\'',
            newState: MultiLangGreetingStubInterface::class,
        );
        self::assertEquals('fr', $multiLangGreetingWorld->language);
    }

    public function testMultipleModifyCalls_Pass(): void
    {
        /** @var V8World<\stdClass> $world */
        $world = $this->world;
        $greetingWorld = $world->modify('hello = (n) => `Hello ${n}!`', newState: GreetingStubInterface::class);
        $multiLangGreetingWorld = $greetingWorld->modify(
            'language = \'fr\'',
            newState: MultiLangGreetingStubInterface::class,
        );
        self::assertEquals('fr', $multiLangGreetingWorld->language); // MultiLangGreetingStubInterface
        self::assertEquals('Hello Bob!', $multiLangGreetingWorld->hello('Bob')); // GreetingStubInterface
    }
}
