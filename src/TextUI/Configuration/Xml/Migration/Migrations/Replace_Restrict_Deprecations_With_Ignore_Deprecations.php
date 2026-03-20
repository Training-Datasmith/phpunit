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
final readonly class Replace_Restrict_Deprecations_With_Ignore_Deprecations implements Migration
{
    /**
     * @throws MigrationException
     */
    public function migrate(Dom_Document $document): void
    {
        $source = $document->get_elements_by_tag_name('source')->item(0);
        if ($source === null) {
            return;
        }
        assert($source instanceof Dom_Element);
        if (!$source->has_attribute('restrictDeprecations')) {
            return;
        }
        $restrict_deprecations = $source->get_attribute('restrictDeprecations') === 'true';
        $source->remove_attribute('restrictDeprecations');
        if (!$restrict_deprecations || $source->has_attribute('ignoreIndirectDeprecations')) {
            return;
        }
        $source->set_attribute('ignoreIndirectDeprecations', 'true');
    }
}