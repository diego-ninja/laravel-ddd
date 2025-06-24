<?php

namespace Modules\Auth\Infrastructure\Http\Endpoints;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Application\Commands\RefreshTokenCommand;
use Modules\Shared\UI\Http\Endpoints\AbstractWriteEndpoint;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

/**
 * JWT token refresh endpoint.
 *
 * Refreshes an existing JWT token, returning a new token with updated expiration.
 */
#[OpenApi\PathItem]
final class RefreshTokenEndpoint extends AbstractWriteEndpoint
{
    #[OpenApi\Operation]
    #[OpenApi\SecurityRequirement('BearerTokenSecurityScheme')]
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->error('Token not provided', 401);
        }

        $command = new RefreshTokenCommand(token: $token);
        $result = $this->executeCommand($command);

        return $this->success($result->toArray());
    }
}
