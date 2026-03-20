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
namespace Php_Unit\Text_Ui\Xml_Configuration;

use Php_Unit\Util\Xml\Xml_Exception;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
abstract readonly class Schema_Detection_Result
{
    /**
     * @phpstan-assert-if-true SuccessfulSchemaDetectionResult $this
     */
    public function detected(): bool
    {
        return false;
    }
    /**
     * @throws XmlException
     */
    public function version(): string
    {
        throw new Xml_Exception('No supported schema was detected');
    }
}