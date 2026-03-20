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
namespace Php_Unit\Framework;

use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Generator_Not_Supported_Exception extends InvalidArgumentException
{
    public static function from_parameter_name(string $parameter_name): self
    {
        return new self(sprintf('Passing an argument of type Generator for the %s parameter is not supported', $parameter_name));
    }
}