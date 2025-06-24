<?php

namespace Modules\Auth\Infrastructure\Http\Endpoints;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Application\Commands\LoginUserCommand;
use Modules\Shared\UI\Http\Endpoints\AbstractWriteEndpoint;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;
/**
 * User login endpoint.
 *
 * Authenticates user with email and password, returning a JWT token on success.
 *
 * @unauthenticated
 */
#[OpenApi\PathItem]
final class LoginEndpoint extends AbstractWriteEndpoint
{
    #[OpenApi\Operation]
    public function __invoke(Request $request): JsonResponse
    {
        $command = new LoginUserCommand(
            email: $request->input('email'),
            password: $request->input('password')
        );

        return $this->executeCommand($command);
    }
}
