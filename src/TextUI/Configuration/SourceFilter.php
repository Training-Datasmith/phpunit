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
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Source_Filter
{
    private static ?self $instance = null;
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self((new Source_Mapper())->map(Registry::get()->source()));
        }
        return self::$instance;
    }
    /**
     * @param array<non-empty-string, true> $map
     */
    public function __construct(private readonly array $map)
    {
    }
    /**
     * @param non-empty-string $path
     */
    public function includes(string $path): bool
    {
        return isset($this->map[$path]);
    }
}