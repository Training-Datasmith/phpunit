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

use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Invalid_Data_Provider_Exception extends Exception
{
    private ?string $provider_label = null;
    public static function for_exception(Throwable $e, string $provider_label): self
    {
        $exception = new self($e->get_message(), $e->get_code(), $e);
        $exception->provider_label = $provider_label;
        return $exception;
    }
    public function get_provider_label(): ?string
    {
        return $this->provider_label;
    }
}