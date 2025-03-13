<?php

declare(strict_types=1);

use Illuminate\Support\Defer\DeferredCallbackCollection;
use Illuminate\Support\Facades\DB;
use Scheel\QueryRecorder\Tests\Fixtures\Foo;
use Scheel\QueryRecorder\Tests\Fixtures\TestQueryProcessor;
use Scheel\QueryRecorder\Trace\Trace;

it('can get a trace', function (): void {
    expect(Trace::getTrace())->toBeInstanceOf(Trace::class);
});

it('calls the processor', function (): void {
    $processor = new TestQueryProcessor;
    DB::recordQueries($processor);
    expect($processor->called)->toBeFalse();
    executeDeferred();
    expect($processor->called)->toBeTrue();
});

it('can provide correct origins for queries', function (): void {
    $processor = new TestQueryProcessor;
    DB::recordQueries($processor);
    (new Foo)->doStuff();
    executeDeferred();
    $records = $processor->records;
    expect($records)->toHaveCount(1)
        ->and($records[0]->sql)->toBe('select * from "test_table" where "id" >= 10')
        ->and($records[0]->origin->location()->endsWith('Foo.php:18'))->toBeTrue();
});

function executeDeferred(): void
{
    app()[DeferredCallbackCollection::class]->invoke();
}
