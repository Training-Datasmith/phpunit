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
namespace Php_Unit\Runner\Extension;

use function array_key_exists;
use Php_Unit\Runner\Parameter_Does_Not_Exist_Exception;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Parameter_Collection
{
    /**
     * @param array<string, string> $parameters
     */
    public static function from_array(array $parameters): self
    {
        return new self($parameters);
    }
    /**
     * @param array<string, string> $parameters
     */
    private function __construct(private array $parameters)
    {
    }
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }
    /**
     * @throws ParameterDoesNotExistException
     */
    public function get(string $name): string
    {
        if (!$this->has($name)) {
            throw new Parameter_Does_Not_Exist_Exception($name);
        }
        return $this->parameters[$name];
    }
}