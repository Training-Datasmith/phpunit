<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class UsesNamespace extends Metadata
{
    /**
     * @param non-empty-string $namespace
     */
    protected function __construct(Level $level, private string $namespace)
    {
        parent::__construct($level);
    }

    public function isUsesNamespace(): true
    {
        return true;
    }

    /**
     * @return class-string
     */
    public function namespace(): string
    {
        return $this->namespace;
    }
}
