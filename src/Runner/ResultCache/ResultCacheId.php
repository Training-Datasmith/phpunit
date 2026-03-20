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
namespace Php_Unit\Runner\Result_Cache;

use Php_Unit\Event\Code\Test;
use Php_Unit\Event\Code\Test_Method;
use Php_Unit\Framework\Reorderable;
use Php_Unit\Framework\Test_Case;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Result_Cache_Id
{
    public static function from_test(Test $test): self
    {
        if ($test instanceof Test_Method) {
            return new self($test->class_name() . '::' . $test->name());
        }
        return new self($test->id());
    }
    public static function from_reorderable(Reorderable $reorderable): self
    {
        return new self($reorderable->sort_id());
    }
    /**
     * For use in PHPUnit tests only!
     *
     * @param class-string<TestCase> $class
     */
    public static function from_test_class_and_method_name(string $class, string $method_name): self
    {
        return new self($class . '::' . $method_name);
    }
    private function __construct(private string $id)
    {
    }
    public function as_string(): string
    {
        return $this->id;
    }
}