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
use Dom_Element;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Rename_Force_Covers_Annotation_Attribute implements Migration
{
    public function migrate(Dom_Document $document): void
    {
        $root = $document->document_element;
        assert($root instanceof Dom_Element);
        if ($root->has_attribute('requireCoverageMetadata')) {
            return;
        }
        if (!$root->has_attribute('forceCoversAnnotation')) {
            return;
        }
        $root->set_attribute('requireCoverageMetadata', $root->get_attribute('forceCoversAnnotation'));
        $root->remove_attribute('forceCoversAnnotation');
    }
}