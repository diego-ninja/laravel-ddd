<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Laravel DDD API',
    description: 'A comprehensive API for the Laravel DDD starter kit implementing Domain-Driven Design principles with CQRS and Event Sourcing patterns.'
)]
#[OA\Server(url: 'http://localhost:8000', description: 'Development server')]
#[OA\Server(url: 'https://api.example.com', description: 'Production server')]
abstract class Controller
{
    //
}
