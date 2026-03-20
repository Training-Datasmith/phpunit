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

use ArrayIterator;
use function count;
use Countable;
use Dom_Node;
use Dom_Node_List;
use IteratorAggregate;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @template-implements IteratorAggregate<int, DOMNode>
 */
final class Snapshot_Node_List implements Countable, IteratorAggregate
{
    /**
     * @var list<DOMNode>
     */
    private array $nodes = [];
    /**
     * @param DOMNodeList<DOMNode> $list
     */
    public static function from_node_list(Dom_Node_List $list): self
    {
        $snapshot = new self();
        foreach ($list as $node) {
            $snapshot->nodes[] = $node;
        }
        return $snapshot;
    }
    public function count(): int
    {
        return count($this->nodes);
    }
    /**
     * @return ArrayIterator<int, DOMNode>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->nodes);
    }
}