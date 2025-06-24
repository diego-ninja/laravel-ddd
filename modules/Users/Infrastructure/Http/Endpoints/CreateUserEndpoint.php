<?php

namespace Modules\Users\Infrastructure\Http\Endpoints;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\UI\Http\Endpoints\AbstractWriteEndpoint;
use Modules\Users\Application\Commands\CreateUserCommand;

/**
 * Create a new user.
 *
 * Creates a new user in the system with email, password and optional name.
 * The email must be unique and will be validated for format. Password will be automatically hashed before storage.
 *
 * @authenticated
 */
final class CreateUserEndpoint extends AbstractWriteEndpoint
{
    public function __invoke(Request $request): JsonResponse
    {
        $command = CreateUserCommand::fromRequest($request);
        return $this->executeCommand($command);
    }
}
