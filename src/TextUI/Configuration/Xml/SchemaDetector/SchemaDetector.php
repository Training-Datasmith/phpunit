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

use Php_Unit\Util\Xml\Loader;
use Php_Unit\Util\Xml\Xml_Exception;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Schema_Detector
{
    /**
     * @throws XmlException
     */
    public function detect(string $filename): Schema_Detection_Result
    {
        $document = (new Loader())->load_file($filename);
        $schema_finder = new Schema_Finder();
        foreach ($schema_finder->available() as $candidate) {
            $schema = (new Schema_Finder())->find($candidate);
            if (!(new Validator())->validate($document, $schema)->has_validation_errors()) {
                return new Successful_Schema_Detection_Result($candidate);
            }
        }
        return new Failed_Schema_Detection_Result();
    }
}