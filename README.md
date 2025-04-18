<p align="center">
    <p align="center">
        <a href="https://github.com/mortenscheel/laravel-query-recorder/actions"><img alt="GitHub Workflow Status (master)" src="https://github.com/mortenscheel/laravel-query-recorder/actions/workflows/tests.yml/badge.svg"></a>
        <a href="https://packagist.org/packages/mortenscheel/laravel-query-recorder"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/mortenscheel/laravel-query-recorder"></a>
        <a href="https://packagist.org/packages/mortenscheel/laravel-query-recorder"><img alt="Latest Version" src="https://img.shields.io/packagist/v/mortenscheel/laravel-query-recorder"></a>
        <a href="https://packagist.org/packages/mortenscheel/laravel-query-recorder"><img alt="License" src="https://img.shields.io/packagist/l/mortenscheel/laravel-query-recorder"></a>
    </p>
</p>

# Laravel Query Recorder

A package to record and analyze database queries in Laravel applications.

## Installation

You can install the package via composer:

```shell
composer require mortenscheel/laravel-query-recorder --dev
```

## Usage

This package comes with a couple of recorders.

### Recording all queries to CSV

The package includes a CSV recorder that can write all queries to a CSV file. Example use in a controller:

```php
use Scheel\QueryRecorder\Facades\QueryRecorder;
use Scheel\QueryRecorder\Processors\CsvProcessor;

class UserController extends Controller
{
    public function index()
    {
        // Create a CSV recorder with a file path
        $recorder = new CsvProcessor(storage_path('queries.csv'));

        // Start recording
        QueryRecorder::record($recorder);

        // Your controller logic with database queries
        $users = User::all();
        
        // The CSV will be written after the response has been sent
        // thanks to Laravel's defer() mechanism
        
        return view('users.index', compact('users'));
    }
}
```

The CSV file will include:
- Query time (ms)
- Origin (file and line number)
- SQL query (raw)

### Record duplicate queries to CSV

```php
use Scheel\QueryRecorder\Processors\DuplicateQueryCsvProcessor;

QueryRecorder::record(new DuplicateQueryCsvProcessor('/path/to/output.csv'));
```
This recorder will only show duplicate queries, grouped by both sql and origin.
There will be one row per unique sql+origin, containing:

- Count
- Total time (ms)
- Origin
- SQL

### Custom Recorders

You can create your own custom recorder by implementing the `RecordsQueries` interface:

```php
use Scheel\QueryRecorder\RecordsQueries;
use Scheel\QueryRecorder\QueryCollection;

class CustomProcessor implements QueryCollectionProcessor
{
    public function process(QueryCollection $queries): void
    {
        // Custom implementation to record the queries collection
        // This will be called after the request is complete
    }
}
```

Usage example:

```php
$recorder = new CustomProcessor();
QueryRecorder::record($recorder);

// Execute queries...
DB::table('users')->first();

// The recordQueries method will be called after the response
// has been sent, with all collected queries
```
### Query Origin Tracking

One of the key features of this package is the ability to identify where in your code a query is being executed:

```php
$query->origin->file     // The file path where the query was initiated
$query->origin->line     // The line number where the query was initiated
$query->origin->function // The function that initiated the query
$query->origin->class    // The class that initiated the query (if applicable)
$query->origin->type     // The type of call (static or instance method)

// Additional helper methods
$query->origin->isVendor()   // Check if the query originated from a vendor package
$query->origin->location()   // Get the file:line format
$query->origin->editorLink() // Get an editor link to the exact location
```

This is especially helpful for debugging and optimizing database queries in your application.

## Listening for Queries

This is similar to Laravel's `DB::listen()` except you receive a `RecordedQuery` with extra metadata.

```php
use Scheel\QueryRecorder\Facades\QueryRecorder;

QueryRecorder::listen(function (RecordedQuery $query) {
    // Do something with the recorded query
    Log::debug('Query executed', ['origin' => $query->location(), 'sql' => $query->sql]);
});

// Execute database queries...
DB::table('users')->first();
```

## Testing

```bash
composer test
```

## Security

If you discover any security related issues, please email the author instead of using the issue tracker.

## Credits

- [Morten Scheel](https://github.com/mortenscheel)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
