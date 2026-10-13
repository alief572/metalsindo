# Report SPK Marketing backend regression and benchmark

Standalone CLI runner for PHP 5.6 / MySQL 5.7. It does not depend on PHPUnit 11
(the repository has a PHPUnit config, but its referenced test bootstrap is missing
and PHPUnit 11 does not run on PHP 5.6).

## Run

Set `REPORT_TEST_HOST`, `REPORT_TEST_USER`, `REPORT_TEST_PASSWORD`, and
`REPORT_TEST_DATABASE` in the PHP process environment; credentials are not stored
in these files. The fixture account needs SELECT and CREATE TEMPORARY TABLES.

```sh
php tests/report_spk_marketing/regression.php
php tests/report_spk_marketing/regression.php --benchmark
```

Both commands create only connection-local temporary tables that disappear when
the connection closes. They shadow the report tables without altering real data.
MySQL 5.7 cannot reference the same temporary table twice in one statement, so the
adapter routes sequence and aggregate aliases to identical temporary detail-table
copies. The original SQL is unchanged in the application. Fixture timing is
therefore a synthetic comparison, not a production measurement.

`fixtures/Report_spk_marketing_baseline.php` is the frozen pre-optimization model,
with only its class name changed. Keep this oracle unchanged when modifying the
production model.

## Coverage

- 612 page comparisons: nine filter cases, 17 sort columns, both directions, two offsets.
- 306 unbounded export comparisons; formatted rows also compared.
- NULL/zero deletion flags, approval NULL/0/1, non-deal/deleted details, missing
  lookup records, historical/empty-name customers, mixed-case SPK IDs, all-NULL
  quantities, and literal `%`, `_`, `!`, and apostrophes in document search.
- Empty/out-of-range pages, page-size bounds, duplicate lookup cardinality,
  and read transaction cleanup after an empty page or injected batch exception.
- Controller permissions, JSON response, Indonesian numeric formatting, validation,
  and one count rather than two when applied filters equal the all-data filters.
- Workbook equality and XLSX write/read round trip, including cell types,
  numeric/date formats, 17 columns, merged headers, and frozen panes.
- Performance guards against global quantity aggregates in counts/ordinary page
  selection and unbounded per-row enrichment queries.

Mutation check: temporarily removing item-number computation before sorting made
the suite fail on column 6 ascending at offset 0. The original condition was restored.

## Measured result (2026-10-08)

PHP 5.6 / MySQL 5.7 Docker fixture: 2,000 SPKs and 24,000 detail rows; primary keys
on lookup IDs, and a single-column detail SPK index. Default approved filter,
25 items, newest SPK date first. Both paths warmed up; measurement order alternated;
five measurements per path. Wall time includes reading results and the optimized
page's transaction overhead. This measures model/database work, not HTTP/UI time.

| Work | Before median, ms (range) | After median, ms (range) |
| --- | --- | --- |
| Page data | 87.875 (84.621–95.503) | 29.547 (26.505–29.879) |
| All-data count | 61.094 (52.180–61.323) | 36.979 (35.162–38.864) |
| Filtered count | 30.687 (28.645–36.251) | 16.186 (12.921–19.406) |
| Combined per iteration | 177.843 (172.653–187.437) | 82.059 (76.405–84.929) |

Combined median decreased about 54%. All five optimized samples were faster than
all baseline samples. Combined medians are calculated from each iteration's sum,
not by adding the separate metric medians.

Default loads now issue five SELECTs instead of three: one page, two bounded
enrichment batches, and two counts. BEGIN/COMMIT are additional transaction
commands. The added batches remove substantially more global aggregation work.
The all-data filter reuses the total count and requires one fewer SELECT.

EXPLAIN showed the baseline's derived aggregate scanning approximately 24,308
detail rows in the page query and both counts. Optimized counts and ordinary page
selection contain no derived total aggregate; the page's total batch estimated
36 detail rows, while the item-number batch starts from 25 selected IDs.
EXPLAIN row counts are estimates, not measured rows examined.

## Real-data follow-up and index policy

The local Docker databases contain no real SPK tables. No application schema/index
was changed; actual lookup uniqueness and indexes remain unverified. Lookup joins
are deliberately retained in counts, including their original duplicate behavior.

For read-only profiling on a database containing the actual report tables, set the
same environment variables to a SELECT-only account and run:

```sh
php tests/report_spk_marketing/regression.php --benchmark --existing-data
```

This mode creates no fixture, executes SELECT/SHOW INDEX/EXPLAIN plus transaction
commands, and compares both query paths with five warm samples. Run against a
representative staging copy or during an appropriate profiling window. It fails
the performance gate if the timing ranges overlap, rather than claiming a win
inside measurement variation. EXPLAIN output includes schema/index metadata.

Inspect candidate indexes only after checking existing key prefixes and execution
plans: detail `(id_spkmarketing, deal, deleted, id)` and header indexes supporting
approval/date and customer/date filters. Do not install all candidates blindly.
Add a separate reversible migration only for a missing index whose real-data
benchmark shows a measurable gain; verify write cost as well.

The page transaction keeps page/enrichment reads consistent on InnoDB under
REPEATABLE READ; verify the actual storage engine and isolation before deployment.
Counts retain the previous separate-query freshness semantics. Sorting by item
number/total quantity must still compute that global sort key before LIMIT and
can remain more expensive than ordinary sorts. Full export retains the original
unbounded query and memory behavior. No result cache or date restriction is added.
