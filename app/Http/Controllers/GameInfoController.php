<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\Link;
use Illuminate\Http\JsonResponse;

class GameInfoController extends Controller
{
    public function gameDatas(): JsonResponse
    {
        $genres = Genre::select('id', 'name')->get();
        $platforms = Platform::select('id', 'name')->get();
        $linkLabels = Link::select('label')
            ->distinct()
            ->where('label', '!=', null)
            ->where('label', '!=', '')
            ->pluck('label'); 
        return response()->json([
            'genres' => $genres,
            'platforms' => $platforms,
            'link_labels' => $linkLabels, 
        ]);
    }
}
