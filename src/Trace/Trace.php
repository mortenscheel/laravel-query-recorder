<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Trace;

use Illuminate\Support\Collection;

use function collect;

/** @extends Collection<int, \Scheel\QueryRecorder\Trace\Frame> */
class Trace extends Collection
{
    public static function getTrace(): self
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $frames = collect($backtrace)
            ->filter(fn (array $frame): bool => isset(
                $frame['file'],
                $frame['line'],
            ))
            ->values()
            ->mapInto(Frame::class);

        return self::make($frames);
    }

    public function ignoreVendor(): self
    {
        return $this->filter(fn (Frame $frame): bool => ! $frame->isVendor());
    }

    public function ignoreSelf(): self
    {
        return $this->filter(fn (Frame $frame): bool => $frame->file !== __FILE__ && $frame->class !== self::class);
    }
}
