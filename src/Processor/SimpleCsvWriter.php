<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Processor;

use Illuminate\Support\Collection;
use RuntimeException;
use Scheel\QueryRecorder\RecordedQuery;

use function collect;
use function fclose;
use function fopen;
use function fputcsv;

// @codeCoverageIgnoreStart
readonly class SimpleCsvWriter implements QueryRecordProcessor
{
    public function __construct(
        private string $path,
    ) {}

    public function process(array $records): void
    {
        $fh = fopen($this->path, 'wb');
        if ($fh === false) {
            throw new RuntimeException('Could not open file for writing: '.$this->path);
        }
        /** @var Collection<string, Collection<int, float>> $dupesMap */
        $dupesMap = collect($records)
            ->mapToGroups(fn (RecordedQuery $record) => [$record->sql => $record->time]);
        fputcsv($fh, ['Time', 'Occurrences', 'Total time', 'Origin', 'SQL']);
        foreach ($records as $record) {
            $occurrences = $dupesMap->get($record->sql);
            /** @var array<int, string|int|float> $data */
            $data = [
                $record->time,
                $occurrences?->count() ?? 1,
                $occurrences?->sum() ?? 0,
                $record->origin->location(),
                $record->sql,
            ];
            fputcsv($fh, $data);
        }
        fclose($fh);
    }
}
// @codeCoverageIgnoreEnd
