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
namespace Php_Unit\Framework\Mock_Object\Generator;

use Sebastian_Bergmann\Type\Type;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Hooked_Property
{
    private Type $type;
    /**
     * @param non-empty-string $name
     */
    public function __construct(private string $name, Type $type, private bool $get_hook, private bool $set_hook, private ?Type $setter_type)
    {
        $this->type = $type;
    }
    public function name(): string
    {
        return $this->name;
    }
    public function type(): Type
    {
        return $this->type;
    }
    public function has_get_hook(): bool
    {
        return $this->get_hook;
    }
    public function has_set_hook(): bool
    {
        return $this->set_hook;
    }
    /**
     * @throws RuntimeException
     */
    public function setter_type(): Type
    {
        if ($this->setter_type === null) {
            throw new RuntimeException();
        }
        return $this->setter_type;
    }
}