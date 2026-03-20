<?php

declare (strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Php_Unit\Metadata;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_With extends Metadata
{
    /**
     * @param ?non-empty-string $name
     */
    protected function __construct(Level $level, private mixed $data, private ?string $name = null)
    {
        parent::__construct($level);
    }
    public function is_test_with(): true
    {
        return true;
    }
    public function data(): mixed
    {
        return $this->data;
    }
    /**
     * @phpstan-assert-if-true !null $this->name
     */
    public function has_name(): bool
    {
        return $this->name !== null;
    }
    /**
     * @return ?non-empty-string
     */
    public function name(): ?string
    {
        return $this->name;
    }
}