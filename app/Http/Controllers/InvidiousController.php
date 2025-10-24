<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InvidiousService;

class InvidiousController extends Controller
{
    protected $invidious;

    public function __construct(InvidiousService $invidious)
    {
        

    }
}
