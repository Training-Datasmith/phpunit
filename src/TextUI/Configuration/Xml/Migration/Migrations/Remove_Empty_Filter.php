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

use Dom_Document;
use Dom_Element;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Remove_Empty_Filter implements Migration
{
    /**
     * @throws MigrationException
     */
    public function migrate(Dom_Document $document): void
    {
        $whitelist = $document->get_elements_by_tag_name('whitelist')->item(0);
        if ($whitelist instanceof Dom_Element) {
            $this->ensure_empty($whitelist);
            $whitelist->parent_node->remove_child($whitelist);
        }
        $filter = $document->get_elements_by_tag_name('filter')->item(0);
        if ($filter instanceof Dom_Element) {
            $this->ensure_empty($filter);
            $filter->parent_node->remove_child($filter);
        }
    }
    /**
     * @throws MigrationException
     */
    private function ensure_empty(Dom_Element $element): void
    {
        if ($element->attributes->length > 0) {
            throw new Migration_Exception(sprintf('%s element has unexpected attributes', $element->node_name));
        }
        if ($element->get_elements_by_tag_name('*')->length > 0) {
            throw new Migration_Exception(sprintf('%s element has unexpected children', $element->node_name));
        }
    }
}