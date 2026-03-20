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
namespace Php_Unit\Runner;

use RuntimeException;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Class_Is_Abstract_Exception extends RuntimeException implements Exception
{
    public function __construct(string $class_name, string $file)
    {
        parent::__construct(sprintf('Class %s declared in %s is abstract', $class_name, $file));
    }
}