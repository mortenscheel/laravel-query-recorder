<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Recorders;

use Illuminate\Support\Collection;
use Scheel\QueryRecorder\QueryCollection;
use Scheel\QueryRecorder\RecordedQuery;

use function fputcsv;

readonly class DuplicateQueryCsvRecorder implements RecordsQueries
{
    public function __construct(private string $path) {}

    public function recordQueries(QueryCollection $queries): void
    {
        /** @var Collection<int, array<string, string>> $rows */
        $rows = $queries->toBase()
            // Group by identical queries
            ->groupBy('sql')
            // Remove unique queries
            ->filter(fn (Collection $group): bool => $group->count() > 1)
            // Group identical queries further by their origin
            ->map(
                fn (Collection $group) => $group->groupBy(fn (RecordedQuery $item): string => $item->origin->location())
                    ->sortByDesc(fn (Collection $group): int => $group->count())
            )->sortByDesc(fn (Collection $sqlGroup) => $sqlGroup->sum(fn (Collection $originGroup): int => $originGroup->count()))
            ->tap(function (Collection $temp): void {
                $a = $temp->toArray();
            })
            // Map to CSV rows
            ->map(fn (Collection $sqlGroup) => $sqlGroup->map(function (Collection $originGroup): array {
                $record = $originGroup->firstOrFail();
                $occurrences = $originGroup->count();
                /** @var float $time */
                $time = $originGroup->sum('time');
                $relativePath = str($record->origin->file)->after(base_path('/'))->value();
                $line = $record->origin->line;

                return [
                    'Occurrences' => $occurrences,
                    'Total time' => (string) round($time, 2),
                    'Origin' => "$relativePath:$line",
                    'SQL' => $record->sql,
                ];
            }))->flatten(1);
        if ($rows->isNotEmpty()) {
            /** @var resource $fh */
            $fh = fopen($this->path, 'wb');
            fputcsv($fh, ['Count', 'Time', 'Origin', 'SQL']);
            foreach ($rows as $row) {
                fputcsv($fh, $row);
            }
            fclose($fh);
        }
    }
}
