<?php

namespace Modules\Auth\Infrastructure\Http\Endpoints;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Application\Commands\LogoutUserCommand;
use Modules\Shared\UI\Http\Endpoints\AbstractWriteEndpoint;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

/**
 * User logout endpoint.
 *
 * Invalidates the current JWT token, logging the user out.
 */
#[OpenApi\PathItem]
final class LogoutEndpoint extends AbstractWriteEndpoint
{
    #[OpenApi\Operation]
    #[OpenApi\SecurityRequirement('BearerTokenSecurityScheme')]
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->error('Token not provided', 401);
        }

        $command = new LogoutUserCommand(token: $token);
        $this->executeCommand($command);

        return $this->success(['message' => 'Successfully logged out']);
    }
}
