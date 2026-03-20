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

use Lib_Xml_Error;
use const PHP_EOL;
use function sprintf;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Validation_Result
{
    /**
     * @param array<int, LibXMLError> $errors
     */
    public static function from_array(array $errors): self
    {
        $validation_errors = [];
        foreach ($errors as $error) {
            if (!isset($validation_errors[$error->line])) {
                $validation_errors[$error->line] = [];
            }
            $validation_errors[$error->line][] = trim($error->message);
        }
        return new self($validation_errors);
    }
    /**
     * @param array<int, list<string>> $validationErrors
     */
    private function __construct(private array $validation_errors)
    {
    }
    public function has_validation_errors(): bool
    {
        return $this->validation_errors !== [];
    }
    public function as_string(): string
    {
        $buffer = '';
        foreach ($this->validation_errors as $line => $validation_errors_on_line) {
            $buffer .= sprintf(PHP_EOL . '  Line %d:' . PHP_EOL, $line);
            foreach ($validation_errors_on_line as $validation_error) {
                $buffer .= sprintf('  - %s' . PHP_EOL, $validation_error);
            }
        }
        return $buffer;
    }
}