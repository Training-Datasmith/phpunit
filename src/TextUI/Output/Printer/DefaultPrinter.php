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
namespace Php_Unit\Text_Ui\Output;

use function assert;
use function count;
use function dirname;
use function explode;
use function fclose;
use function fopen;
use function fsockopen;
use function fwrite;
use Php_Unit\Runner\Directory_Does_Not_Exist_Exception;
use Php_Unit\Text_Ui\Cannot_Open_Socket_Exception;
use Php_Unit\Text_Ui\Invalid_Socket_Exception;
use Php_Unit\Util\Filesystem;
use function str_replace;
use function str_starts_with;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Default_Printer implements Printer
{
    /**
     * @var closed-resource|resource
     */
    private $stream;
    private readonly bool $is_php_stream;
    private bool $is_open;
    /**
     * @throws CannotOpenSocketException
     * @throws DirectoryDoesNotExistException
     * @throws InvalidSocketException
     */
    public static function from(string $out): self
    {
        return new self($out);
    }
    /**
     * @throws CannotOpenSocketException
     * @throws DirectoryDoesNotExistException
     * @throws InvalidSocketException
     */
    public static function standard_output(): self
    {
        return new self('php://stdout');
    }
    /**
     * @throws CannotOpenSocketException
     * @throws DirectoryDoesNotExistException
     * @throws InvalidSocketException
     */
    public static function standard_error(): self
    {
        return new self('php://stderr');
    }
    /**
     * @throws CannotOpenSocketException
     * @throws DirectoryDoesNotExistException
     * @throws InvalidSocketException
     */
    private function __construct(string $out)
    {
        $this->is_php_stream = str_starts_with($out, 'php://');
        if (str_starts_with($out, 'socket://')) {
            $tmp = explode(':', str_replace('socket://', '', $out));
            if (count($tmp) !== 2) {
                throw new Invalid_Socket_Exception($out);
            }
            $stream = @fsockopen($tmp[0], (int) $tmp[1]);
            if ($stream === false) {
                throw new Cannot_Open_Socket_Exception($tmp[0], (int) $tmp[1]);
            }
            $this->stream = $stream;
            $this->is_open = true;
            return;
        }
        if (!$this->is_php_stream && !Filesystem::create_directory(dirname($out))) {
            throw new Directory_Does_Not_Exist_Exception(dirname($out));
        }
        $stream = fopen($out, 'wb');
        assert($stream !== false);
        $this->stream = $stream;
        $this->is_open = true;
    }
    public function print(string $buffer): void
    {
        assert($this->is_open);
        fwrite($this->stream, $buffer);
    }
    public function flush(): void
    {
        if ($this->is_open && $this->is_php_stream) {
            fclose($this->stream);
            $this->is_open = false;
        }
    }
}