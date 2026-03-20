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

use function assert;
use Dom_Document;
use function file_get_contents;
use function libxml_clear_errors;
use function libxml_get_errors;
use function libxml_use_internal_errors;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Validator
{
    public function validate(Dom_Document $document, string $xsd_filename): Validation_Result
    {
        $buffer = file_get_contents($xsd_filename);
        assert($buffer !== false);
        $original_error_handling = libxml_use_internal_errors(true);
        $document->schema_validate_source($buffer);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($original_error_handling);
        return Validation_Result::from_array($errors);
    }
}