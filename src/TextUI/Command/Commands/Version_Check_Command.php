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

use function assert;
use const PHP_EOL;
use Php_Unit\Util\Http\Downloader;
use function sprintf;
use function version_compare;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Version_Check_Command implements Command
{
    public function __construct(private Downloader $downloader, private int $major_version_number, private string $version_id)
    {
    }
    public function execute(): Result
    {
        $latest_version = $this->downloader->download('https://phar.phpunit.de/latest-version-of/phpunit');
        assert($latest_version !== false);
        $latest_compatible_version = $this->downloader->download('https://phar.phpunit.de/latest-version-of/phpunit-' . $this->major_version_number);
        $not_latest = version_compare($latest_version, $this->version_id, '>');
        $not_latest_compatible = false;
        if ($latest_compatible_version !== false) {
            $not_latest_compatible = version_compare($latest_compatible_version, $this->version_id, '>');
        }
        if (!$not_latest && !$not_latest_compatible) {
            return Result::from('You are using the latest version of PHPUnit.' . PHP_EOL);
        }
        $buffer = 'You are not using the latest version of PHPUnit.' . PHP_EOL;
        if ($not_latest_compatible) {
            $buffer .= sprintf('The latest version compatible with PHPUnit %s is PHPUnit %s.' . PHP_EOL, $this->version_id, $latest_compatible_version);
        }
        if ($not_latest) {
            $buffer .= sprintf('The latest version is PHPUnit %s.' . PHP_EOL, $latest_version);
        }
        return Result::from($buffer, Result::FAILURE);
    }
}