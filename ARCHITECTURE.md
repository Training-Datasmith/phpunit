# PHPUnit Architecture

## Purpose

PHPUnit is the de-facto standard PHP testing framework, implementing the xUnit architecture. It provides the infrastructure for writing and running unit, integration, and end-to-end tests, collecting results, and reporting outcomes.

## Directory Structure

```
src/
├── Event/           — Event system (Emitter, Dispatcher, typed event objects)
├── Framework/       — Core testing primitives
│   ├── Assert/      — Static assertion methods and helper functions
│   ├── Constraint/  — Composable constraint objects used by assertions
│   ├── Exception/   — Framework-level exceptions (IncompleteTest, SkippedTest, etc.)
│   ├── MockObject/  — Test double (mock/stub/spy) generation infrastructure
│   ├── TestRunner/  — Executes a single TestCase within the current process
│   ├── TestSize/    — Categorisation of test size (small / medium / large)
│   └── TestStatus/  — Typed value objects representing pass/fail/skip/error states
├── Logging/         — Result output formatters (JUnit XML, TeamCity, etc.)
├── Metadata/        — Attribute and annotation parser for test metadata
├── Runner/          — CLI runner, test suite discovery, parallel execution, PHPT support
├── TextUI/          — Command-line interface, configuration loading, output rendering
└── Util/            — Internal utilities (reflection helpers, XML, colour output)
```

## Key Design Decisions

- **Value objects for test status** — `TestStatus` and `TestSize` are immutable value objects, preventing accidental mutation of test results during a run.
- **Event-driven architecture** — All significant moments in a test run (preparation, assertion failure, teardown) emit typed events through `Event\Facade::emitter()`, allowing extension without subclassing.
- **Constraint composition** — Assertions delegate to composable `Constraint` objects (`LogicalAnd`, `LogicalOr`, `LogicalNot`), making complex assertions readable.
- **Isolated process support** — `TestCase::run()` detects `#[RunInSeparateProcess]` and delegates to `IsolatedTestRunnerRegistry`, serialising the test case and resuming in a child process.
- **Readonly internals** — Internal state (method name, data set) is injected through the constructor and never mutated after construction.

## Extension Points

- **Custom constraints** — Extend `Constraint\Constraint` and implement `matches()` and `toString()`.
- **Custom comparators** — Implement `SebastianBergmann\Comparator\Comparator` and register via `ComparatorFactory`.
- **Event subscribers** — Register `Event\Subscriber` implementations to observe run progress without modifying core behaviour.
- **Custom result printers** — Implement a subscriber that reacts to `Event\Test\*` events and writes its own output.

## Dependency Flow

```
TextUI (CLI entry point)
  └─► Runner (orchestrates discovery and execution)
        ├─► Framework\TestSuite (collection of tests)
        │     └─► Framework\TestCase (individual test)
        │           ├─► Framework\Assert (static assertions)
        │           └─► Framework\MockObject (test doubles)
        ├─► Event\Facade (broadcasts events)
        └─► Logging (formatters receive events)
```

## Versioning Notes

- PHPUnit 13 targets PHP ≥ 8.4.
- The backward compatibility promise covers public, non-`@internal` API only.
- Metadata is read from PHP 8 attributes (preferred) with fallback to docblock annotations.
