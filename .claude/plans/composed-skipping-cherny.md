# Plan: Add Unit Tests

## Context

The bundle has no tests. Adding PHPUnit 13 tests for all classes to ensure correctness and enable safe refactoring.

## Test Structure

```
tests/
├── Entity/
│   └── AuditLogTest.php
├── Enum/
│   └── AuditLogActionTest.php
├── Event/
│   └── EventTest.php              # All 6 events in one test (identical pattern)
├── EventSubscriber/
│   ├── AuditLogEventSubscriberTest.php
│   ├── BaseEventSubscribeTest.php
│   └── EventSubscriberTest.php    # All 7 Symfony event subscribers (identical pattern)
├── DependencyInjection/
│   ├── ConfigurationTest.php
│   └── AuditLogExtensionTest.php
├── Controller/
│   └── AuditLogControllerTest.php
└── Repository/
    └── AuditLogRepositoryTest.php
```

## Test Details

### 1. `tests/Entity/AuditLogTest.php`
- Setter chaining (all setters return `self`)
- Getter/setter roundtrip for every property
- `initializeCreatedAt()` — sets DateTime when not set, preserves existing
- `getId()` returns null initially

### 2. `tests/Enum/AuditLogActionTest.php`
- All 8 cases exist (Create, Edit, List, View, Archive, Restore, Export, Login)
- String values match expected lowercase names

### 3. `tests/Event/EventTest.php`
- For each of 6 event classes (CreateEvent, ArchiveEvent, ExportEvent, ListEvent, RestoreEvent, ViewEvent):
  - Constructor stores entity
  - `getEntity()` returns the same entity

### 4. `tests/EventSubscriber/AuditLogEventSubscriberTest.php`
Most complex — Doctrine lifecycle listener. Mock `EntityManagerInterface`, `UnitOfWork`, `TokenStorageInterface`, `LifecycleEventArgs`.
- `postPersist()` — ignores non-AwareAuditLogInterface entities
- `postPersist()` — creates audit log for AwareAuditLogInterface entity
- `postUpdate()` — detects archive (archivedAt becomes truthy → Archive action)
- `postUpdate()` — detects restore (archivedAt becomes falsy → Restore action)
- `postUpdate()` — creates edit entries for field changes
- `resolveFieldValue()` — DateTime → string, array → JSON, scalar → string (test via reflection since private)

### 5. `tests/EventSubscriber/BaseEventSubscribeTest.php`
Test via a concrete anonymous/test subclass. Mock `AuditLogRepository`, `TokenStorageInterface`, `RequestStack`.
- `saveLog()` — creates and persists AuditLog with correct data
- `saveLog()` — skips 'prometheus' user
- `saveLog()` — resolves user from token when no user param
- `saveLog()` — uses passed user over token user
- `saveLog()` — handles null entity
- `saveLog()` — gets client IP from request

### 6. `tests/EventSubscriber/EventSubscriberTest.php`
All 7 Symfony EventSubscriberInterface subscribers share identical pattern. Test via `@dataProvider`:
- `getSubscribedEvents()` returns correct event→method mapping
- Action property is set correctly
- Log method calls `saveLog()` with correct arguments

Covers: CreateEventSubscriber, ArchiveEventSubscriber, ExportEventSubscriber, ListEventSubscriber, ViewEventSubscriber, RestoreEventSubscriber, LoginSuccessEventSubscriber

### 7. `tests/DependencyInjection/ConfigurationTest.php`
- Tree builder root node is 'audit_log'
- All 8 event boolean nodes exist under `events`
- All default to false

### 8. `tests/DependencyInjection/AuditLogExtensionTest.php`
- `load()` with all events disabled — no event subscriber services registered
- `load()` with each event enabled — correct subscriber class registered
- Registered services are autowired, autoconfigured, public
- `getConfiguration()` returns `Configuration` instance

### 9. `tests/Controller/AuditLogControllerTest.php`
- `lists()` calls repository `findAll()` and renders template with items

### 10. `tests/Repository/AuditLogRepositoryTest.php`
- `add()` calls `persist()`, does not flush by default
- `add(flush: true)` calls `persist()` + `flush()`
- `remove()` calls `remove()`, does not flush by default
- `remove(flush: true)` calls `remove()` + `flush()`
- `update()` calls `flush()`

## Verification

```bash
vendor/bin/phpunit
vendor/bin/phpstan analyse --no-progress
```
