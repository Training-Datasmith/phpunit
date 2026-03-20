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
namespace Php_Unit\Util\PHP;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Job
{
    /**
     * @param non-empty-string       $code
     * @param list<string>           $phpSettings
     * @param array<string, string>  $environmentVariables
     * @param list<non-empty-string> $arguments
     * @param ?non-empty-string      $input
     */
    public function __construct(private string $code, private array $php_settings = [], private array $environment_variables = [], private array $arguments = [], private ?string $input = null, private bool $redirect_errors = false, private bool $requires_xdebug = false)
    {
    }
    /**
     * @return non-empty-string
     */
    public function code(): string
    {
        return $this->code;
    }
    /**
     * @return list<string>
     */
    public function php_settings(): array
    {
        return $this->php_settings;
    }
    /**
     * @phpstan-assert-if-true !empty $this->environmentVariables
     */
    public function has_environment_variables(): bool
    {
        return $this->environment_variables !== [];
    }
    /**
     * @return array<string, string>
     */
    public function environment_variables(): array
    {
        return $this->environment_variables;
    }
    /**
     * @phpstan-assert-if-true !empty $this->arguments
     */
    public function has_arguments(): bool
    {
        return $this->arguments !== [];
    }
    /**
     * @return list<non-empty-string>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }
    /**
     * @phpstan-assert-if-true !empty $this->input
     */
    public function has_input(): bool
    {
        return $this->input !== null;
    }
    /**
     * @throws PhpProcessException
     *
     * @return non-empty-string
     */
    public function input(): string
    {
        if ($this->input === null) {
            throw new Php_Process_Exception('No input specified');
        }
        return $this->input;
    }
    public function redirect_errors(): bool
    {
        return $this->redirect_errors;
    }
    public function requires_xdebug(): bool
    {
        return $this->requires_xdebug;
    }
}