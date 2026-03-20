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
namespace Php_Unit\Util;

use function in_array;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Version_Comparison_Operator
{
    /**
     * @var '!='|'<'|'<='|'<>'|'='|'=='|'>'|'>='|'eq'|'ge'|'gt'|'le'|'lt'|'ne'
     */
    private string $operator;
    /**
     * @param '!='|'<'|'<='|'<>'|'='|'=='|'>'|'>='|'eq'|'ge'|'gt'|'le'|'lt'|'ne' $operator
     *
     * @throws InvalidVersionOperatorException
     */
    public function __construct(string $operator)
    {
        $this->ensure_operator_is_valid($operator);
        $this->operator = $operator;
    }
    /**
     * @return '!='|'<'|'<='|'<>'|'='|'=='|'>'|'>='|'eq'|'ge'|'gt'|'le'|'lt'|'ne'
     */
    public function as_string(): string
    {
        return $this->operator;
    }
    /**
     * @param '!='|'<'|'<='|'<>'|'='|'=='|'>'|'>='|'eq'|'ge'|'gt'|'le'|'lt'|'ne' $operator
     *
     * @throws InvalidVersionOperatorException
     */
    private function ensure_operator_is_valid(string $operator): void
    {
        if (!in_array($operator, ['<', 'lt', '<=', 'le', '>', 'gt', '>=', 'ge', '==', '=', 'eq', '!=', '<>', 'ne'], true)) {
            throw new Invalid_Version_Operator_Exception($operator);
        }
    }
}