<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InvidiousService;

class InvidiousController extends Controller
{
    protected $invidious;

    public function __construct(InvidiousService $invidious)
    {
        $this->invidious = $invidious;
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json(['error' => 'Missing search query'], 400);
        }

        $results = $this->invidious->search($query);
        return response()->json($results);
    }
}
