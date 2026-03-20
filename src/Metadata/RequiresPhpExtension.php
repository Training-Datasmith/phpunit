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
namespace Php_Unit\Metadata;

use Php_Unit\Metadata\Version\Requirement;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Requires_Php_Extension extends Metadata
{
    /**
     * @param non-empty-string $extension
     */
    protected function __construct(Level $level, private string $extension, private ?Requirement $version_requirement)
    {
        parent::__construct($level);
    }
    public function is_requires_php_extension(): true
    {
        return true;
    }
    /**
     * @return non-empty-string
     */
    public function extension(): string
    {
        return $this->extension;
    }
    /**
     * @phpstan-assert-if-true !null $this->versionRequirement
     */
    public function has_version_requirement(): bool
    {
        return $this->version_requirement !== null;
    }
    /**
     * @throws NoVersionRequirementException
     */
    public function version_requirement(): Requirement
    {
        if ($this->version_requirement === null) {
            throw new No_Version_Requirement_Exception();
        }
        return $this->version_requirement;
    }
}