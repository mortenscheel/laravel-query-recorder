<?php

declare(strict_types=1);

use Illuminate\Support\Defer\DeferredCallbackCollection;
use Illuminate\Support\Facades\DB;
use Scheel\QueryRecorder\Facades\QueryRecorder;
use Scheel\QueryRecorder\RecordedQuery;
use Scheel\QueryRecorder\Recorders\CsvQueryRecorder;
use Scheel\QueryRecorder\Tests\Fixtures\Foo;

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

it('can provide correct origins for queries', function (): void {
    $detected = null;
    QueryRecorder::listen(function (RecordedQuery $query) use (&$detected): void {
        $detected = $query;
    });
    (new Foo)->doStuff();
    expect($detected)
        ->toBeInstanceOf(RecordedQuery::class)
        ->and($detected->origin->file)->toBe((new ReflectionClass(Foo::class))->getFileName())
        ->and($detected->origin->line)->toBe(18);
});

it('can record queries to csv', function (): void {
    $stream = tmpfile();
    $path = stream_get_meta_data($stream)['uri'];
    fclose($stream);
    $recorder = new CsvQueryRecorder($path);
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
    $recorder = new CsvQueryRecorder($path);
    QueryRecorder::record($recorder);
    executeDeferred();
    expect(file_exists($path))->toBeFalse();
});

function executeDeferred(): void
{
    app()[DeferredCallbackCollection::class]->invoke();
}
