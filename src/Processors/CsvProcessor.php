<?php

declare(strict_types=1);

namespace Scheel\QueryRecorder\Processors;

use Scheel\QueryRecorder\QueryCollection;
use Scheel\QueryRecorder\RecordedQuery;

use function fclose;
use function fopen;
use function fputcsv;

readonly class CsvProcessor implements QueryCollectionProcessor
{
    public function __construct(private string $path) {}

    public function process(QueryCollection $queries): void
    {
        if ($queries->isEmpty()) {
            return;
        }
        /** @var resource $fh */
        $fh = fopen($this->path, 'wb');
        $queries->toBase()->map(fn (RecordedQuery $record): array => [
            (string) $record->time,
            $record->origin->location(),
            $record->sql,
        ])
            ->prepend(['Time', 'Origin', 'SQL'])
            ->each(function (array $row) use ($fh): void {
                fputcsv($fh, $row);
            });
        fclose($fh);
    }
}
