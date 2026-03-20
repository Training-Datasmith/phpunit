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
namespace Php_Unit\Logging\Test_Dox;

use Php_Unit\Event\Code\Test_Method;
use Php_Unit\Event\Code\Throwable;
use Php_Unit\Framework\Test_Status\Test_Status;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Test_Result
{
    public function __construct(private Test_Method $test, private Test_Status $status, private ?Throwable $throwable)
    {
    }
    public function test(): Test_Method
    {
        return $this->test;
    }
    public function status(): Test_Status
    {
        return $this->status;
    }
    /**
     * @phpstan-assert-if-true !null $this->throwable
     */
    public function has_throwable(): bool
    {
        return $this->throwable !== null;
    }
    public function throwable(): ?Throwable
    {
        return $this->throwable;
    }
}