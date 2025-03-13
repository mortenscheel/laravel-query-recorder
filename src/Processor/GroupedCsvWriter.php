<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Processor;

use Illuminate\Support\Collection;
use RuntimeException;
use Scheel\QueryRecorder\RecordedQuery;

use function fclose;
use function fopen;
use function fputcsv;

// @codeCoverageIgnoreStart
readonly class GroupedCsvWriter implements QueryRecordProcessor
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
        fputcsv($fh, ['Occurrences', 'Total time', 'Top origin', 'SQL']);
        /** @var Collection<string, Collection<int, RecordedQuery>> $grouped */
        $grouped = collect($records)
            ->mapToGroups(fn (RecordedQuery $record) => [$record->sql => $record]);
        foreach ($grouped as $sql => $groupRecords) {
            $total = $groupRecords->sum('event.time');
            // Find the most common origin of these events
            $origin = $groupRecords->groupBy(fn (RecordedQuery $record): string => $record->origin->location()->value())
                ->map(fn (Collection $occurrences): int => $occurrences->count())
                ->sortDesc()
                ->keys()
                ->first();
            /** @var array<int, int|float|string> $data */
            $data = [$groupRecords->count(), $total, $origin, $sql];
            fputcsv($fh, $data);
        }
        fclose($fh);
    }
}
// @codeCoverageIgnoreEnd
