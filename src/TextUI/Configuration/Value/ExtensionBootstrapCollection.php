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

use IteratorAggregate;
/**
 * @template-implements IteratorAggregate<non-negative-int, ExtensionBootstrap>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Extension_Bootstrap_Collection implements IteratorAggregate
{
    /**
     * @var list<ExtensionBootstrap>
     */
    private array $extension_bootstraps;
    /**
     * @param list<ExtensionBootstrap> $extensionBootstraps
     */
    public static function from_array(array $extension_bootstraps): self
    {
        return new self(...$extension_bootstraps);
    }
    private function __construct(Extension_Bootstrap ...$extension_bootstraps)
    {
        $this->extension_bootstraps = $extension_bootstraps;
    }
    /**
     * @return list<ExtensionBootstrap>
     */
    public function as_array(): array
    {
        return $this->extension_bootstraps;
    }
    public function getIterator(): Extension_Bootstrap_Collection_Iterator
    {
        return new Extension_Bootstrap_Collection_Iterator($this);
    }
}