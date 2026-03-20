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
namespace Php_Unit\Framework\Mock_Object\Generator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Method_Named_Method_Exception extends \Php_Unit\Framework\Exception implements Exception
{
    public function __construct()
    {
        parent::__construct('Doubling interfaces (or classes) that have a method named "method" is not supported.');
    }
}