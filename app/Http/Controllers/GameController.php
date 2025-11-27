<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::with([
            'mainImage',
            'screenshots',
            'genres',
            'platforms',
            'links'
        ])->get();

        return response()->json($games->map(function ($game) {
            return $this->formatGame($game);
        }));
    }

    public function show($id)
    {
        $game = Game::with([
            'mainImage',
            'screenshots',
            'genres',
            'platforms',
            'links'
        ])->findOrFail($id);

        return response()->json($this->formatGame($game));
    }

    public function store(Request $request)
    {

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'about_game' => 'nullable|string',
                'trailer_link' => 'nullable|url',
                'main_image' => 'nullable|image|max:4096',
                'screenshots' => 'nullable|array',
                'screenshots.*' => 'image|max:4096',
                'genres' => 'required|array',
                'genres.*' => 'string',
                'platforms' => 'required|array',
                'platforms.*' => 'string',
                'links' => 'nullable|string',
            ]);

            $game = Game::create($request->only(['name', 'about_game', 'trailer_link']));

            if ($request->filled('genres')) {
                $genreIds = [];
                foreach ($request->genres as $genreName) {
                    $genre = Genre::firstOrCreate(['name' => trim($genreName)]);
                    $genreIds[] = $genre->id;
                }
                $game->genres()->sync($genreIds);
            }

            if ($request->filled('platforms')) {
                $platformIds = [];
                foreach ($request->platforms as $platformName) {
                    $platform = Platform::firstOrCreate(['name' => trim($platformName)]);
                    $platformIds[] = $platform->id;
                }
                $game->platforms()->sync($platformIds);
            }

            if ($request->hasFile('main_image')) {
                $path = $request->file('main_image')->store('games/main', 'public');
                $game->images()->create([
                    'path' => $path,
                    'is_main' => true,
                    'sort_order' => 0,
                ]);
            }

            if ($request->hasFile('screenshots')) {
                $screenshots = $request->file('screenshots');
                foreach ($screenshots as $i => $file) {
                    $path = $file->store('games/screenshots', 'public');
                    $game->images()->create([
                        'path' => $path,
                        'is_main' => false,
                        'sort_order' => $i + 1,
                    ]);
                }
            }

            if ($request->filled('links')) {
                $linkData = json_decode($request->links, true);
                $linkIds = [];
                
                if (is_array($linkData)) {
                    foreach ($linkData as $linkItem) {
                        if (isset($linkItem['url']) && filter_var($linkItem['url'], FILTER_VALIDATE_URL)) {
                            $link = Link::firstOrCreate(
                                ['url' => $linkItem['url']],
                                ['label' => $linkItem['label'] ?? null]
                            );
                            $linkIds[] = $link->id;
                        }
                    }
                }
                
                $game->links()->sync($linkIds);
            }

            $game->load(['mainImage', 'screenshots', 'genres', 'platforms', 'links']);
            return response()->json($this->formatGame($game), 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Game creation failed',
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $game = Game::findOrFail($id);

        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'about_game' => 'nullable|string',
                'trailer_link' => 'nullable|url',
                'main_image' => 'nullable|image|max:4096',
                'screenshots' => 'nullable|array',
                'screenshots.*' => 'image|max:4096',
                'genres' => 'array',
                'genres.*' => 'string',
                'platforms' => 'array',
                'platforms.*' => 'string',
                'links' => 'nullable|string', 
                'delete_main_image' => 'nullable|boolean',
                'keep_screenshots' => 'nullable|array',
            ]);
            $game->update($request->only(['name', 'about_game', 'trailer_link']));

            if ($request->filled('genres')) {
                $genreIds = [];
                foreach ($request->genres as $genreName) {
                    $genre = Genre::firstOrCreate(['name' => trim($genreName)]);
                    $genreIds[] = $genre->id;
                }
                $game->genres()->sync($genreIds);
            }

            if ($request->filled('platforms')) {
                $platformIds = [];
                foreach ($request->platforms as $platformName) {
                    $platform = Platform::firstOrCreate(['name' => trim($platformName)]);
                    $platformIds[] = $platform->id;
                }
                $game->platforms()->sync($platformIds);
            }

            if ($request->has('delete_main_image')) {
                $oldMain = $game->mainImage;
                if ($oldMain) {
                    Storage::disk('public')->delete($oldMain->path);
                    $oldMain->delete();
                }
            }

            if ($request->hasFile('main_image')) {
                $oldMain = $game->mainImage;
                if ($oldMain && !$request->has('delete_main_image')) {
                    Storage::disk('public')->delete($oldMain->path);
                    $oldMain->delete();
                }

                $path = $request->file('main_image')->store('games/main', 'public');
                $game->images()->create([
                    'path' => $path,
                    'is_main' => true,
                    'sort_order' => 0,
                ]);
            }

            if ($request->has('keep_screenshots')) {
                $keepIds = $request->input('keep_screenshots', []);
                $toDelete = $game->screenshots->whereNotIn('id', $keepIds);
                foreach ($toDelete as $img) {
                    Storage::disk('public')->delete($img->path);
                    $img->delete();
                }
            }

            if ($request->hasFile('screenshots')) {
                $maxOrder = $game->screenshots->max('sort_order') ?? 0;
                foreach ($request->file('screenshots') as $i => $file) {
                    $path = $file->store('games/screenshots', 'public');
                    $game->images()->create([
                        'path' => $path,
                        'is_main' => false,
                        'sort_order' => $maxOrder + $i + 1,
                    ]);
                }
            }

            if ($request->filled('links')) {
                $linkData = json_decode($request->input('links'), true);
                $linkIds = [];

                if (is_array($linkData)) {
                    foreach ($linkData as $linkItem) {
                        if (!isset($linkItem['url']) || !filter_var($linkItem['url'], FILTER_VALIDATE_URL)) {
                            continue;
                        }

                        $url = trim($linkItem['url']);
                        $label = isset($linkItem['label']) ? trim($linkItem['label']) : null;
                        $id = $linkItem['id'] ?? null;

                        $link = null;

                        if ($id) {
                            $link = Link::find($id);
                            if ($link) {
                                if ($link->url !== $url || $link->label !== $label) {
                                    $link->update([
                                        'url' => $url,
                                        'label' => $label,
                                    ]);
                                }
                            } else {
                                $id = null;
                            }
                        }

                        if (!$link) {
                            $link = Link::where('url', $url)->first();
                            if ($link) {
                                if ($link->label !== $label) {
                                    $link->update(['label' => $label]);
                                }
                            } else {
                                $link = Link::create([
                                    'url' => $url,
                                    'label' => $label,
                                ]);
                            }
                        }

                        $linkIds[] = $link->id;
                    }
                }

                $game->links()->sync($linkIds);
            }

            $game->load(['mainImage', 'screenshots', 'genres', 'platforms', 'links']);
            return response()->json($this->formatGame($game));
            
        } catch (\Exception $e) {   
            return response()->json([
                'error' => 'Game update failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $game = Game::findOrFail($id);
        $game->delete();
        return response()->json(null, 204);
    }

    private function formatGame($game)
    {
        return [
            'id' => $game->id,
            'name' => $game->name,
            'about_game' => $game->about_game,
            'trailer_link' => $game->trailer_link,
            'platforms' => $game->platforms->pluck('name'),
            'genres' => $game->genres->pluck('name'),
            'links' => $game->links->map(function ($link) {
                return ['url' => $link->url, 'label' => $link->label];
            }),
            'main_image' => $game->mainImage ? [
                'id' => $game->mainImage->id,
                'url' => asset('storage/' . $game->mainImage->path)
            ] : null,
            'screenshots' => $game->screenshots->map(function ($img) {
                return [
                    'id' => $img->id,
                    'url' => asset('storage/' . $img->path)
                ];
            })
        ];
    }
}