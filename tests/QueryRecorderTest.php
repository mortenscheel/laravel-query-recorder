<?php

declare(strict_types=1);

use Illuminate\Support\Defer\DeferredCallbackCollection;
use Illuminate\Support\Facades\DB;
use Scheel\QueryRecorder\Facades\QueryRecorder;
use Scheel\QueryRecorder\Processors\CsvProcessor;
use Scheel\QueryRecorder\Processors\DuplicateQueryCsvProcessor;
use Scheel\QueryRecorder\Processors\QueryCollectionProcessor;
use Scheel\QueryRecorder\QueryCollection;
use Scheel\QueryRecorder\RecordedQuery;
use Scheel\QueryRecorder\Tests\Fixtures\DummyClass;
use Scheel\QueryRecorder\Tests\Fixtures\NullProcessor;

arch()->preset()->php();
arch()->preset()->security();

it('can listen for queries', function (): void {
    $detected = null;
    QueryRecorder::listen(function (RecordedQuery $query) use (&$detected): void {
        $detected = $query;
    });
    expect($detected)->toBeNull();
    DB::table('test_table')->first();
    $queryLine = __LINE__ - 1;
    expect($detected)
        ->toBeInstanceOf(RecordedQuery::class)
        ->and($detected->sql)->toBe('select * from "test_table" limit 1')
        ->and($detected->origin->file)->toBe(__FILE__)
        ->and($detected->origin->line)->toBe($queryLine);
});

it('can record queries', function (): void {
    $recorder = QueryRecorder::record(new NullProcessor);
    expect($recorder->getQueries())->toHaveCount(0);
    DB::table('test_table')->first();
    expect($recorder->getQueries())->toHaveCount(1);
});

it('can provide correct origins for queries', function (): void {
    $detected = null;
    QueryRecorder::listen(function (RecordedQuery $query) use (&$detected): void {
        $detected = $query;
    });
    (new DummyClass)->doStuff();
    expect($detected)
        ->toBeInstanceOf(RecordedQuery::class)
        ->and($detected->origin->file)->toBe((new ReflectionClass(DummyClass::class))->getFileName())
        ->and($detected->origin->line)->toBe(18);
});

it('can record queries to csv', function (): void {
    $stream = tmpfile();
    $path = stream_get_meta_data($stream)['uri'];
    fclose($stream);
    $recorder = new CsvProcessor($path);
    QueryRecorder::record($recorder);
    DB::table('test_table')->first();
    $firstQueryLine = __LINE__ - 1;
    DB::table('test_table')->latest()->first();
    $secondQueryLine = __LINE__ - 1;
    executeDeferred();
    $stream = fopen($path, 'rb');
    $header = fgetcsv($stream);
    [,$origin1, $sql1] = fgetcsv($stream);
    [,$origin2, $sql2] = fgetcsv($stream);
    fclose($stream);
    expect($header)->toBe(['Time', 'Origin', 'SQL'])
        ->and($origin1)->toBe(__FILE__.':'.$firstQueryLine)
        ->and($sql1)->toBe('select * from "test_table" limit 1')
        ->and($origin2)->toBe(__FILE__.':'.$secondQueryLine)
        ->and($sql2)->toBe('select * from "test_table" order by "created_at" desc limit 1');
    unlink($path);
});

it('does not record csv if no queries were recorded', function (): void {
    $stream = tmpfile();
    $path = stream_get_meta_data($stream)['uri'];
    fclose($stream);
    $recorder = new CsvProcessor($path);
    QueryRecorder::record($recorder);
    executeDeferred();
    expect(file_exists($path))->toBeFalse();
});

it('can record queries using a custom recorder', function (): void {
    $recorder = new class implements QueryCollectionProcessor
    {
        public function __construct(public ?QueryCollection $queries = null) {}

        public function process(QueryCollection $queries): void
        {
            $this->queries = $queries;
        }
    };
    QueryRecorder::record($recorder);
    DB::table('test_table')->first();
    expect($recorder->queries)->toBeNull();
    executeDeferred();
    expect($recorder->queries)->toBeInstanceOf(QueryCollection::class);
});
it('can record duplicate queries', function (): void {
    $stream = tmpfile();
    $path = stream_get_meta_data($stream)['uri'];
    fclose($stream);
    $recorder = new DuplicateQueryCsvProcessor($path);
    QueryRecorder::record($recorder);
    $dummy = new DummyClass;
    $dummy->doStuff();
    $dummy->doStuff();
    $dummy->getById(3);
    $dummy->getById(3);
    $dummy->getById(4);
    $dummy->getById(3);
    $dummy->getById(4);
    $dummy->getById(5);
    $dummy->doStuff();
    $dummy->doStuff();

    executeDeferred();
    $recorded = [];
    $stream = fopen($path, 'rb');
    fgets($stream); // Skip header
    /** @var array<int, string> $row */
    while (($row = fgetcsv($stream)) !== false) {
        // Convert origin to relative path for comparison
        $file = str($row[2])->beforeLast(':')->basename();
        $line = str($row[2])->afterLast(':')->value();

        $recorded[] = [
            'count' => $row[0],
            'origin' => "$file:$line",
            'sql' => $row[3],
        ];
    }
    expect($recorded)->toBe([
        [
            'count' => '4',
            'origin' => 'DummyClass.php:18',
            'sql' => 'select * from "test_table" where "id" >= 10',
        ],
        [
            'count' => '3',
            'origin' => 'DummyClass.php:23',
            'sql' => 'select * from "test_table" where "id" = 3',
        ],
        [
            'count' => '2',
            'origin' => 'DummyClass.php:23',
            'sql' => 'select * from "test_table" where "id" = 4',
        ],
    ]);

    unlink($path);
});

function executeDeferred(): void
{
    app()[DeferredCallbackCollection::class]->invoke();
}
