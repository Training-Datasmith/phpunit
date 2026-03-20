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

use Phar_Io\Version\Version;
use Phar_Io\Version\Version_Constraint;
use function preg_replace;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Constraint_Requirement extends Requirement
{
    private Version_Constraint $constraint;
    public function __construct(Version_Constraint $constraint)
    {
        $this->constraint = $constraint;
    }
    public function is_satisfied_by(string $version): bool
    {
        return $this->constraint->complies(new Version($this->sanitize($version)));
    }
    public function as_string(): string
    {
        return $this->constraint->as_string();
    }
    private function sanitize(string $version): string
    {
        return preg_replace('/^(\d+\.\d+(?:.\d+)?).*$/', '$1', $version);
    }
}