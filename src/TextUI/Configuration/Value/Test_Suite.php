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
namespace Php_Unit\Text_Ui\Configuration;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Test_Suite
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(private string $name, private Test_Directory_Collection $directories, private Test_File_Collection $files, private File_Collection $exclude)
    {
    }
    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }
    public function directories(): Test_Directory_Collection
    {
        return $this->directories;
    }
    public function files(): Test_File_Collection
    {
        return $this->files;
    }
    public function exclude(): File_Collection
    {
        return $this->exclude;
    }
}