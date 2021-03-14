<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class PersonsController extends BaseController
{
    public function index(): JsonResponse
    {
        return response()->json(Person::all());
    }
}
