<?php

namespace Modules\Shared\UI\Http\Endpoints;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Shared\Application\Contracts\Command;
use Modules\Shared\Application\Contracts\CommandBus;
use Modules\Shared\UI\Http\Traits\ResponseTrait;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractWriteEndpoint extends Controller
{
    use ResponseTrait;

    public function __construct(protected CommandBus $commandBus)
    {
    }

    protected function executeCommand(Command $command): JsonResponse
    {
        try {
            $result = $this->commandBus->dispatch($command);
            return $this->success($result, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
