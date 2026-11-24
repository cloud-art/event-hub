<?php

namespace App;

use App\Content\EventProjection;
use Symfony\Component\HttpFoundation\JsonResponse;

final class EventController
{
    public function catalog(): JsonResponse
    {
        return new JsonResponse((new EventProjection())->published());
    }

    public function detail(string $id): JsonResponse
    {
        $event = (new EventProjection())->find($id);
        return $event === null ? new JsonResponse(['error' => 'not_found'], 404) : new JsonResponse($event);
    }
}
