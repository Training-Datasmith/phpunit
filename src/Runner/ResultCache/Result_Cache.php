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
namespace Php_Unit\Runner\Result_Cache;

use Php_Unit\Framework\Test_Status\Test_Status;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This interface is not covered by the backward compatibility promise for PHPUnit
 */
interface Result_Cache
{
    public function set_status(Result_Cache_Id $id, Test_Status $status): void;
    public function status(Result_Cache_Id $id): Test_Status;
    public function set_time(Result_Cache_Id $id, float $time): void;
    public function time(Result_Cache_Id $id): float;
    public function load(): void;
    public function persist(): void;
}