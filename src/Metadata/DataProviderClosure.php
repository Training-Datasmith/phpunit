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

use Closure;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DataProviderClosure extends Metadata
{
    protected function __construct(Level $level, private Closure $closure, private bool $validateArgumentCount)
    {
        parent::__construct($level);
    }

    public function isDataProviderClosure(): true
    {
        return true;
    }

    public function closure(): Closure
    {
        return $this->closure;
    }

    public function validateArgumentCount(): bool
    {
        return $this->validateArgumentCount;
    }
}
