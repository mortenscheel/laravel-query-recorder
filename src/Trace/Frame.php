<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Trace;

use Illuminate\Support\Stringable;

use function str_contains;

class Frame
{
    public string $file;

    public int $line;

    public string $function;

    public ?string $class;

    public ?string $type = null;

    /**
     * @param  array{file: string, line: int, function: string, class?: string, type?: string}  $frame
     */
    public function __construct(
        array $frame,
    ) {
        $this->file = $frame['file'];
        $this->line = $frame['line'];
        $this->function = $frame['function'];
        $this->class = $frame['class'] ?? null;
        $this->type = $frame['type'] ?? null;
    }

    public function isVendor(): bool
    {
        return str_contains($this->file, DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR);
    }

    public function location(): Stringable
    {
        return str("$this->file:$this->line");
    }
}
