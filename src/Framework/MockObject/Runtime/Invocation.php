<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework\MockObject;

use function array_map;
use function implode;

use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Util\Exporter;

use function sprintf;
use function str_starts_with;
use function strtolower;
use function substr;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Invocation implements SelfDescribing
{
    private string $returnType;
    private bool $isReturnTypeNullable;

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     * @param array<mixed>     $parameters
     */
    public function __construct(private string $className, private string $methodName, private array $parameters, string $returnType, private MockObjectInternal|StubInternal $object)
    {
        if (strtolower($this->methodName) === '__tostring') {
            $returnType = 'string';
        }

        if (str_starts_with($returnType, '?')) {
            $returnType                 = substr($returnType, 1);
            $this->isReturnTypeNullable = true;
        } else {
            $this->isReturnTypeNullable = false;
        }

        $this->returnType = $returnType;
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * @return non-empty-string
     */
    public function methodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return array<mixed>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @throws Exception
     */
    public function generateReturnValue(): mixed
    {
        if ($this->returnType === 'never') {
            throw new NeverReturningMethodException(
                $this->className,
                $this->methodName,
            );
        }

        if ($this->isReturnTypeNullable) {
            return null;
        }

        return (new ReturnValueGenerator())->generate(
            $this->className,
            $this->methodName,
            $this->object,
            $this->returnType,
        );
    }

    public function toString(): string
    {
        return sprintf(
            '%s::%s(%s)%s',
            $this->className,
            $this->methodName,
            implode(
                ', ',
                array_map(
                    Exporter::shortenedExport(...),
                    $this->parameters,
                ),
            ),
            $this->returnType !== '' ? sprintf(': %s', $this->returnType) : '',
        );
    }

    public function object(): MockObjectInternal|StubInternal
    {
        return $this->object;
    }
}
