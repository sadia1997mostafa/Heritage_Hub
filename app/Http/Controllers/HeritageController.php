<?php

namespace App\Http\Controllers;

use App\Models\Heritage;
use App\Services\WikipediaHeritageService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HeritageController extends Controller
{
    public function show(Request $request, string $division, ?string $district = null)
    {
        [$division, $district, $items, $cached] = $this->getItems($division, $district);

        return response()->json([
            'division' => $division,
            'district' => $district,
            'items'    => $items,
            'cached'   => $cached,
        ]);
    }

   
    public function page(Request $request, string $division, ?string $district = null)
    {
        $force = (bool) $request->boolean('force');

        if ($force) {
            // Drop cache for this scope, then refill
            $division = ucfirst(strtolower($division));
            $district = $district ? ucfirst(strtolower($district)) : null;

            $query = Heritage::query()->where('division', $division);
            $district ? $query->where('district', $district) : $query->whereNull('district');
            $query->delete();
        }

        [$division, $district, $items, $cached] = $this->getItems($division, $district);

        return view('heritage.index', [
            'division' => $division,
            'district' => $district,
            'items'    => $items,
            'cached'   => $cached,
            'forced'   => $force,
        ]);
    }

    
    private function getItems(string $division, ?string $district = null): array
    {
        $division = ucfirst(strtolower($division));
        $district = $district ? ucfirst(strtolower($district)) : null;

        $staleBefore = Carbon::now()->subDays(30);

        // Try DB cache
        $q = Heritage::query()->where('division', $division);
        $district ? $q->where('district', $district) : $q->whereNull('district');
        $existing = $q->orderBy('created_at','desc')->get();

        if ($existing->isNotEmpty() && $existing->first()->fetched_at && $existing->first()->fetched_at->greaterThan($staleBefore)) {
            $items = $existing->map(fn($h)=>[
                'title'=>$h->title,
                'category'=>$h->category,
                'summary'=>$h->summary,
                'image_url'=>$h->image_url,
                'wiki_url'=>$h->wiki_url,
                'lat'=>$h->lat,
                'lon'=>$h->lon,
            ])->values()->all();

            return [$division, $district, $items, true];
        }

        // Fetch fresh, then save
        $service = app(WikipediaHeritageService::class);
        $items = $service->fetchFor($division, $district, limit: 8);

        $q->delete();
        foreach ($items as $it) {
            Heritage::create([
                'division'  => $division,
                'district'  => $district,
                'title'     => $it['title'],
                'category'  => $it['category'],
                'summary'   => $it['summary'],
                'image_url' => $it['image_url'],
                'wiki_url'  => $it['wiki_url'],
                'lat'       => $it['lat'],
                'lon'       => $it['lon'],
                'fetched_at'=> now(),
            ]);
        }

        return [$division, $district, $items, false];
    }
}
