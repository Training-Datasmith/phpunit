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
namespace Php_Unit\Text_Ui\Command;

use function copy;
use function file_put_contents;
use const PHP_EOL;
use Php_Unit\Text_Ui\Xml_Configuration\Migrator;
use function sprintf;
use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Migrate_Configuration_Command implements Command
{
    public function __construct(private string $filename)
    {
    }
    public function execute(): Result
    {
        try {
            $migrated = (new Migrator())->migrate($this->filename);
            copy($this->filename, $this->filename . '.bak');
            file_put_contents($this->filename, $migrated);
            return Result::from(sprintf('Created backup:         %s.bak%sMigrated configuration: %s%s', $this->filename, PHP_EOL, $this->filename, PHP_EOL));
        } catch (Throwable $t) {
            return Result::from(sprintf('Migration of %s failed:%s%s%s', $this->filename, PHP_EOL, $t->get_message(), PHP_EOL), Result::FAILURE);
        }
    }
}