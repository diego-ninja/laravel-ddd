<?php

namespace Modules\Shared\Application\Middleware\Command;

use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Modules\Shared\Application\Contracts\Middleware;

final readonly class AuditMiddleware implements Middleware
{
    public function __construct(
        private DatabaseManager $database,
        private Request $request
    ) {}

    /**
     * @throws \Throwable
     */
    public function handle(object $message, \Closure $next): mixed
    {
        $auditId = $this->createAuditRecord($message);

        try {
            $result = $next($message);
            $this->updateAuditRecord($auditId, 'success');
            return $result;

        } catch (\Throwable $exception) {
            $this->updateAuditRecord($auditId, 'failed', $exception->getMessage());
            throw $exception;
        }
    }

    private function createAuditRecord(object $command): int
    {
        return $this->database->table('command_audit')->insertGetId([
            'command_class' => $command::class,
            'command_data' => json_encode($this->extractCommandData($command)),
            'user_id' => auth()->id(),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'status' => 'executing',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function updateAuditRecord(int $auditId, string $status, ?string $error = null): void
    {
        $this->database->table('command_audit')
            ->where('id', $auditId)
            ->update([
                'status' => $status,
                'error_message' => $error,
                'completed_at' => now(),
                'updated_at' => now()
            ]);
    }

    private function extractCommandData(object $command): array
    {
        $reflection = new \ReflectionClass($command);
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPublic()) {
                $data[$property->getName()] = $property->getValue($command);
            }
        }

        return $data;
    }
}
