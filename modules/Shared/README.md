# Shared Module

This module contains all the shared components and abstractions used across different bounded contexts in the Laravel DDD application.

## Components

### Domain Layer
- **AggregateRoot**: Base class for all aggregate roots with domain event support
- **DomainEvent**: Base class for all domain events with UUID and timestamp
- **BaseValueObject**: Base class for value objects with common functionality
- **DomainException**: Base exception class for domain-specific errors

### Application Layer
- **Bus Interfaces**: CommandBus, QueryBus, EventBus contracts
- **Handler Interfaces**: CommandHandler, QueryHandler contracts
- **Command/Query Interfaces**: Base contracts for CQRS implementation
- **ApplicationException**: Base exception for application layer errors

### Infrastructure Layer
- **SharedServiceProvider**: Service provider for shared components
- **Event Store Migration**: Database schema for domain events
- **InfrastructureException**: Base exception for infrastructure errors

## Usage

All bounded contexts should extend from these shared components:

```php
// Domain Entity
class Order extends AggregateRoot
{
    // Implementation
}

// Domain Event
class OrderWasPlaced extends DomainEvent
{
    // Implementation
}

// Value Object
class OrderId extends BaseValueObject
{
    // Implementation
}

// Domain Exception
class SalesDomainException extends DomainException
{
    // Implementation
}
```

## Installation

This module is automatically loaded when you create a new bounded context using the DDD artisan commands.