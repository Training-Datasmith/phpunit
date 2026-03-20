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
namespace Php_Unit\Metadata\Version;

use Phar_Io\Version\Unsupported_Version_Constraint_Exception;
use Phar_Io\Version\Version_Constraint_Parser;
use Php_Unit\Metadata\Invalid_Version_Requirement_Exception;
use Php_Unit\Util\Invalid_Version_Operator_Exception;
use Php_Unit\Util\Version_Comparison_Operator;
use function preg_match;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class Requirement
{
    private const string VERSION_COMPARISON = "/(?P<operator>!=|<|<=|<>|=|==|>|>=)?\\s*(?P<version>[\\d\\.-]+(dev|(RC|alpha|beta)[\\d\\.])?)[ \t]*\r?\$/m";
    /**
     * @throws InvalidVersionOperatorException
     * @throws InvalidVersionRequirementException
     */
    public static function from(string $version_requirement): self
    {
        try {
            return new Constraint_Requirement((new Version_Constraint_Parser())->parse($version_requirement));
        } catch (Unsupported_Version_Constraint_Exception) {
            if (preg_match(self::VERSION_COMPARISON, $version_requirement, $matches) > 0) {
                return new Comparison_Requirement($matches['version'], new Version_Comparison_Operator($matches['operator'] !== '' ? $matches['operator'] : '>='));
            }
        }
        throw new Invalid_Version_Requirement_Exception();
    }
    abstract public function is_satisfied_by(string $version): bool;
    abstract public function as_string(): string;
}