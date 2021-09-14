<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Repositories\PersonsRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class PersonsController extends BaseController
{
    private PersonsRepository $repository;

    public function __construct(PersonsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->repository->getAll());
    }
}
