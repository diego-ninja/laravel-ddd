<?php

namespace Modules\Sales\Application\Commands\PlaceOrder;

use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFactory;
use Modules\Sales\Domain\Exceptions\SalesDomainException;

class PlaceOrderCommandValidator
{
    /**
     * Validate the PlaceOrder command.
     */
    public function validate(PlaceOrderCommand $command): void
    {
        $validator = $this->createValidator($command);

        if ($validator->fails()) {
            throw SalesDomainException::invalidData(
                'Command validation failed: ' . implode(', ', $validator->errors()->all())
            );
        }

        // Additional business validation
        $this->validateBusinessLogic($command);
    }

    /**
     * Create Laravel validator instance.
     */
    private function createValidator(PlaceOrderCommand $command): Validator
    {
        return ValidatorFactory::make($command->toArray(), $this->rules());
    }

    /**
     * Get validation rules.
     */
    private function rules(): array
    {
        return [
            // Add validation rules here
            // 'property' => 'required|string|max:255',
            // 'email' => 'required|email',
            // 'amount' => 'required|numeric|min:0',
        ];
    }

    /**
     * Validate business logic.
     */
    private function validateBusinessLogic(PlaceOrderCommand $command): void
    {
        // Add complex business validation here
        // Example: Check if user has permission, validate business constraints, etc.
    }
}