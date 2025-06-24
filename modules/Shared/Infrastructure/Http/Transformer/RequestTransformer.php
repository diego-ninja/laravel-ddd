<?php

namespace Modules\Shared\Infrastructure\Http\Transformer;

use Illuminate\Http\Request;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;

final readonly class RequestTransformer
{
    public static function transform(Request $request): ServerRequestInterface
    {
        $factory = new Psr17Factory();

        $uri = $factory->createUri($request->fullUrl());
        $method = $request->method();

        $psrRequest = $factory->createServerRequest($method, $uri);

        foreach ($request->headers->all() as $name => $values) {
            $psrRequest = $psrRequest->withHeader($name, $values);
        }

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $body = $factory->createStream($request->getContent());
            $psrRequest = $psrRequest->withBody($body);
        }

        if ($request->query->count() > 0) {
            $psrRequest = $psrRequest->withQueryParams($request->query->all());
        }

        return $psrRequest;
    }
}
