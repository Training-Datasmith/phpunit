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
final readonly class UsesClassesThatImplementInterface extends Metadata
{
    /**
     * @param class-string $interfaceName
     */
    protected function __construct(Level $level, private string $interfaceName)
    {
        parent::__construct($level);
    }

    public function isUsesClassesThatImplementInterface(): true
    {
        return true;
    }

    /**
     * @return class-string
     */
    public function interfaceName(): string
    {
        return $this->interfaceName;
    }
}
