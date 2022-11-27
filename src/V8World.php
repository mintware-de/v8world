<?php

declare(strict_types=1);

namespace MintwareDe\V8World;

use V8Js;

/**
 * @template T
 *
 * @mixin T
 * @mixin V8Js
 */
class V8World
{
    private function __construct(
        private readonly V8Js $instance,
    ) {
    }

    /**
     * Creates a new V8World wrapper for the given V8Js instance
     *
     * @param V8Js $instance
     * @param class-string<T> $initialState The initial state
     * @return V8World<T>&static
     */
    public static function create(V8Js $instance, string $initialState = \stdClass::class): static
    {
        /** @phpstan-var static $world */
        $world = new V8World($instance);
        return $world;
    }

    /**
     * Modify the world.
     * Keep in mind that the return value is the same instance but differently typed!
     *
     * @template T2
     * @param class-string<T2> $newState
     * @return static&V8World<T2>&T2
     */
    public function modify(
        string $script,
        string $identifier = '',
        int $flags = V8Js::FLAG_NONE,
        int $timeLimit = 0,
        int $memoryLimit = 0,
        string $newState = self::class,
    ): static {
        $this->instance->executeString(
            $script,
            $identifier,
            $flags,
            $timeLimit,
            $memoryLimit
        );
        /** @phpstan-var static&V8World<T2>&T2 $self */
        $self = $this;
        return $self;
    }

    /**
     * Returns the V8js instance
     */
    public function getInstance(): V8Js
    {
        return $this->instance;
    }

    public function __get(string $name): mixed
    {
        return $this->instance->executeString($name);
    }

    /**
     * @param string $name Method name
     * @param mixed[] $arguments Method arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this->instance, $name)) {
            return $this->instance->{$name}(...$arguments);
        }
        /** @phpstan-ignore-next-line */
        return $this->instance->executeString($name)(...$arguments);
    }
}
