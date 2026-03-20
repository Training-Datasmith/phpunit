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
namespace Php_Unit\Runner;

use function array_map;
use function usort;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Hook_Method_Collection
{
    /**
     * @var non-empty-list<HookMethod>
     */
    private array $hook_methods;
    public static function default_before_class(): self
    {
        return new self(new Hook_Method('setUpBeforeClass', 0), true);
    }
    public static function default_before(): self
    {
        return new self(new Hook_Method('setUp', 0), true);
    }
    public static function default_pre_condition(): self
    {
        return new self(new Hook_Method('assertPreConditions', 0), true);
    }
    public static function default_post_condition(): self
    {
        return new self(new Hook_Method('assertPostConditions', 0), false);
    }
    public static function default_after(): self
    {
        return new self(new Hook_Method('tearDown', 0), false);
    }
    public static function default_after_class(): self
    {
        return new self(new Hook_Method('tearDownAfterClass', 0), false);
    }
    private function __construct(Hook_Method $default, private readonly bool $should_prepend)
    {
        $this->hook_methods = [$default];
    }
    public function add(Hook_Method $hook_method): self
    {
        if ($this->should_prepend) {
            $this->hook_methods = [$hook_method, ...$this->hook_methods];
        } else {
            $this->hook_methods[] = $hook_method;
        }
        return $this;
    }
    /**
     * @return list<non-empty-string>
     */
    public function method_names_sorted_by_priority(): array
    {
        $hook_methods = $this->hook_methods;
        usort($hook_methods, static fn(Hook_Method $a, Hook_Method $b): int => $b->priority() <=> $a->priority());
        return array_map(static fn(Hook_Method $hook_method): string => $hook_method->method_name(), $hook_methods);
    }
}