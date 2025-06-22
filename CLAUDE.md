# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **Laravel DDD Starter Kit** - a comprehensive boilerplate implementing Domain-Driven Design (DDD) principles in a **Modular Monolith** architecture. The project provides a complete set of artisan commands to bootstrap DDD applications with CQRS, Event Sourcing, and best practices.

### Key Architectural Concepts

- **Modular Monolith**: Single deployable unit organized into self-contained business modules
- **Bounded Contexts**: Business domains organized as modules (located in `/modules/` directory)
- **DDD Layers**: Each module follows layered architecture (Domain, Application, Infrastructure)
- **CQRS**: Complete Command Query Responsibility Segregation implementation
- **Event Sourcing**: Domain Events with projectors and event store
- **Pure Domain Layer**: Business logic isolated from framework and persistence concerns
- **Shared Module**: Common abstractions and utilities shared across all bounded contexts
- **Auto-Discovery**: Automatic registration of handlers, repositories, and services

## DDD Artisan Commands

This starter kit provides 8 specialized commands for rapid DDD development:

### Core Architecture Commands
```bash
# Create new Bounded Context with complete structure
php artisan ddd:context {ContextName}

# Create Domain Entity with Aggregate Root functionality
php artisan ddd:entity {context} {EntityName}

# Create Value Objects (6 specialized types: string, email, money, date, enum, number)
php artisan ddd:value-object {context} {ValueObjectName} [--type=]

# Create Repository interface + Eloquent implementation + auto-bindings
php artisan ddd:repository {context} {EntityName} [--model]
```

### Event-Driven Design
```bash
# Create Domain Events with optional listeners
php artisan ddd:event {context} {EventName} [--listener]
```

### CQRS Implementation
```bash
# Create Command + Handler + Validator for write operations
php artisan ddd:command {context} {CommandName} [--no-validator]

# Create QueryHandler + ReadModel + optional Projector for read operations
php artisan ddd:query {context} {QueryName} [--projector] [--model=]
```

### Cross-Cutting Concerns
```bash
# Create middleware for command/query/event pipelines
php artisan ddd:middleware {context} {MiddlewareName} {type}
# Types: command, query, event
```

## Development Commands

### PHP/Laravel Commands
```bash
# Development server with full stack (server, queue, logs, frontend)
composer dev

# Run tests
composer test
# Alternative: php artisan test

# Individual artisan commands
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
php artisan migrate
php artisan config:clear
```

### Frontend Commands
```bash
# Development mode with hot reload
npm run dev

# Build for production
npm run build
```

### Testing
- Uses **Pest PHP** testing framework
- Test configuration in `phpunit.xml`
- Test suites: `tests/Unit/` and `tests/Feature/`
- Run tests: `composer test` or `php artisan test`

### Code Quality
- **Laravel Pint** for code formatting (included in require-dev)
- Run formatting: `./vendor/bin/pint`

## Generated Architecture

### Module Structure
```
# Shared Module (Common Abstractions)
modules/Shared/
├── Domain/
│   ├── Entities/
│   │   └── AggregateRoot.php         # Base for all aggregates
│   ├── Events/
│   │   └── DomainEvent.php           # Base for all domain events
│   ├── ValueObjects/
│   │   └── BaseValueObject.php       # Base for all value objects
│   ├── Contracts/                    # Domain interfaces
│   └── Exceptions/
│       └── DomainException.php       # Base domain exception
├── Application/
│   └── Contracts/                    # CQRS interfaces
│       ├── CommandBusInterface.php
│       ├── QueryBusInterface.php
│       ├── EventBusInterface.php
│       ├── CommandInterface.php
│       ├── CommandHandlerInterface.php
│       └── QueryHandlerInterface.php
└── Infrastructure/
    ├── Bus/                          # Laravel bus implementations
    │   ├── LaravelCommandBus.php
    │   ├── LaravelQueryBus.php
    │   └── LaravelEventBus.php
    ├── Support/
    │   └── HandlerDiscovery.php      # Auto-discovery for handlers
    └── Providers/
        └── SharedServiceProvider.php # Shared services & buses

# Bounded Context Module (Example)
modules/{BoundedContext}/
├── Application/                      # Application Layer
│   ├── Commands/                     # CQRS Commands
│   │   └── {CommandName}/
│   │       ├── {Command}Command.php          # implements CommandInterface
│   │       ├── {Command}CommandHandler.php   # implements CommandHandlerInterface
│   │       └── {Command}CommandValidator.php
│   ├── Queries/                      # CQRS Queries
│   │   └── {QueryName}QueryHandler.php      # implements QueryHandlerInterface
│   ├── DTOs/                         # Read Models
│   │   └── {ReadModel}.php
│   ├── Projectors/                   # Event → Read Model
│   │   └── {ReadModel}Projector.php
│   ├── Listeners/                    # Event Handlers
│   │   └── {Event}Listener.php
│   └── Middleware/                   # Cross-cutting concerns
│       ├── Commands/
│       ├── Queries/
│       └── Events/
├── Domain/                           # Domain Layer (Pure)
│   ├── Entities/                     # Aggregates & Entities
│   │   └── {Entity}.php              # extends Shared\AggregateRoot
│   ├── ValueObjects/                 # Value Objects (6 types)
│   │   ├── {Entity}Id.php            # extends Shared\BaseValueObject
│   │   └── {ValueObject}.php         # extends Shared\BaseValueObject
│   ├── Events/                       # Domain Events
│   │   └── {Event}.php               # extends Shared\DomainEvent
│   ├── Repositories/                 # Repository Interfaces
│   │   └── {Entity}RepositoryInterface.php
│   ├── Services/                     # Domain Services
│   └── Exceptions/                   # Domain Exceptions
│       └── {Context}DomainException.php # extends Shared\DomainException
└── Infrastructure/                   # Infrastructure Layer
    ├── Persistence/
    │   ├── Eloquent/
    │   │   └── {Entity}Model.php
    │   └── Eloquent{Entity}Repository.php
    ├── Http/
    │   └── Controllers/
    └── Providers/
        └── {Context}ServiceProvider.php
```

## DDD Patterns Implemented

### Value Objects (6 Specialized Types)
- **String**: Basic validation, length limits
- **Email**: Email validation, domain extraction
- **Money**: Multi-currency arithmetic, precision handling
- **Date**: DateTimeImmutable wrapper, comparisons
- **Enum**: Constrained values, validation
- **Number**: Arithmetic operations, comparisons

### CQRS Features
- **Commands**: Immutable DTOs with validation
- **CommandHandlers**: Business logic orchestration
- **Queries**: Optimized read operations (direct DB, cache, read models)
- **ReadModels**: Immutable presentation DTOs
- **Projectors**: Event-driven read model updates

### Event Sourcing
- **Domain Events**: Base class with UUID, timestamp
- **Event Store**: Persistence middleware
- **Event Replay**: Projector rebuild functionality
- **Event Listeners**: Async processing with queue support

### Middleware Pipeline
- **Command Middleware**: Transactions, auth, validation, logging
- **Query Middleware**: Caching, rate limiting, access control
- **Event Middleware**: Event store, duplicate detection, retry

## Architecture Guidelines

### DDD Principles to Follow
1. **Domain Purity**: Keep domain models free from Laravel/Eloquent dependencies
2. **Repository Pattern**: Use repositories to abstract persistence, implement with Eloquent in Infrastructure layer
3. **Dependency Inversion**: Use interfaces in Domain, implement in Infrastructure
4. **Ubiquitous Language**: Class and method names should reflect business terminology
5. **CQRS Separation**: Commands for writes, Queries for reads
6. **Event-Driven Architecture**: Use Domain Events for side effects and inter-module communication

### Laravel Integration
- Use Service Providers to wire dependencies and configure module boundaries
- Eloquent models should be confined to Infrastructure layer
- Avoid Facades in Domain layer - use explicit dependency injection
- Auto-registration of handlers, repositories, and middleware pipelines
- Laravel buses (Command, Event, Query) configured and ready to use
- Shared module provides common abstractions across all contexts

### Naming Conventions
- **Commands**: Imperative (PlaceOrder, CreateCustomer, UpdateProduct)
- **Events**: Past tense (OrderWasPlaced, CustomerWasCreated)
- **Queries**: Descriptive (GetOrderHistory, FindActiveCustomers)
- **Value Objects**: Descriptive (OrderId, Email, Price)

## Technology Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Vite, TailwindCSS 4.0, Axios
- **Testing**: Pest PHP
- **Database**: SQLite (default), supports PostgreSQL/MySQL
- **Queue**: Laravel Queue system for async event processing
- **UUID**: Ramsey/UUID for domain event identification
- **Styling**: TailwindCSS with Vite integration

## Documentation

Comprehensive DDD implementation guide available in `/docs/` directory:
- Domain-Driven Design concepts and patterns
- Laravel-DDD integration strategies
- Modular Monolith architecture
- CQRS implementation
- Event Sourcing patterns
- Inter-module communication patterns

## Example Workflow

```bash
# 1. Create new bounded context
php artisan ddd:context Sales

# 2. Create domain entities
php artisan ddd:entity Sales Order
php artisan ddd:entity Sales Customer

# 3. Create value objects
php artisan ddd:value-object Sales Price
php artisan ddd:value-object Sales Email

# 4. Create repositories
php artisan ddd:repository Sales Order --model
php artisan ddd:repository Sales Customer

# 5. Create domain events
php artisan ddd:event Sales OrderWasPlaced --listener
php artisan ddd:event Sales CustomerWasCreated

# 6. Create CQRS commands
php artisan ddd:command Sales PlaceOrder
php artisan ddd:command Sales CreateCustomer

# 7. Create CQRS queries
php artisan ddd:query Sales GetOrderHistory --projector
php artisan ddd:query Sales FindActiveCustomers

# 8. Add middleware
php artisan ddd:middleware Sales DatabaseTransaction command
php artisan ddd:middleware Sales Caching query
php artisan ddd:middleware Sales EventStore event
```

## Development Notes

- This project prioritizes business logic modeling over rapid prototyping
- Folder structure deviates from Laravel defaults to support Bounded Contexts
- All generated code follows DDD principles and Laravel best practices
- Auto-configuration reduces boilerplate (repository bindings, service providers)
- Focus on explicit dependencies rather than Laravel "magic"
- Extensive code comments and examples in generated templates

## Shared Module Architecture

The Shared module provides common abstractions that eliminate code duplication across bounded contexts:

### Key Features
- **Base Classes**: AggregateRoot, DomainEvent, BaseValueObject, Exception hierarchies
- **CQRS Interfaces**: Command, Query, Handler interfaces for type safety
- **Laravel Bus Integration**: Configured Command, Query, and Event buses
- **Auto-Discovery**: Automatic registration of handlers across all modules
- **UUID Generation**: Centralized UUID factory for domain events
- **Event Store**: Domain events table with proper indexing

### Usage
All DDD components automatically extend from Shared abstractions:
```php
// Entities extend Shared\AggregateRoot
class Order extends AggregateRoot { }

// Events extend Shared\DomainEvent  
class OrderWasPlaced extends DomainEvent { }

// Value Objects extend Shared\AbstractValueObject
class Price extends BaseValueObject { }

// Handlers implement Shared interfaces
class PlaceOrderHandler implements CommandHandlerInterface { }
```

### Bus System
Commands, queries, and events are dispatched through Laravel's native bus system:
```php
// Inject and use buses
public function __construct(
    private CommandBusInterface $commandBus,
    private QueryBusInterface $queryBus,
    private EventBusInterface $eventBus
) {}

// Dispatch operations
$this->commandBus->dispatch(new PlaceOrderCommand(...));
$result = $this->queryBus->ask(new GetOrderQuery(...));
$this->eventBus->publish(new OrderWasPlaced(...));
```
