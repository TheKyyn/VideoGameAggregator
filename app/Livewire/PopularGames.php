<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class PopularGames extends Component
{
    public $popularGames = [];

    public function loadPopularGames()
    {
        $before = Carbon::now()->subMonths(2)->timestamp;
        $after = Carbon::now()->addMonths(2)->timestamp;

        $popularGamesUnformatted = Cache::remember('popular-games', 7, function() use($before, $after) {
            return HTTP::withHeaders(config('services.igdb'))
            ->withBody("fields name, cover.url, first_release_date, platforms.abbreviation, rating, slug;
            where platforms = (48,49,130,6)
            & (first_release_date >= {$before}
            & first_release_date < {$after})
            & rating > 40;
            sort rating desc;
            limit 12;"
            )
            ->post('https://api.igdb.com/v4/games')
            ->json();
        });
        // dd($popularGamesUnformatted);

        $this->popularGames = $this->formatForView($popularGamesUnformatted);
        // dd($popularGamesUnformatted);
    }

    // protected function fetchPopularGames($before, $after)
    // {
    //     $query = "fields name, cover.url, first_release_date, total_rating_count, platforms.abbreviation, rating, slug;
    //         where platforms = (48,49,130,6)
    //         & (first_release_date >= {$before}
    //         & first_release_date < {$after})
    //         & total_rating_count > 5;
    //         sort total_rating_count desc;
    //         limit 12;";

    //     $popularGamesUnformatted = Http::withHeaders(config('services.igdb'))
    //         ->withBody($query, "text/plain")
    //         ->post('https://api.igdb.com/v4/games')
    //         ->json();

    //     // dd($popularGamesUnformatted);
    //     $this->popularGames = $this->formatForView($popularGamesUnformatted);
    //     dd($query);
    // }


    public function render()
    {
        return view('livewire.popular-games');
    }

    public function formatForView($games)
    {
        return collect($games)->map(function ($game) {
            return collect($game)->merge([
                'name' => $game['name'],
                'coverImageUrl' => Str::replaceFirst('thumb', 'cover_big', $game['cover']['url']),
                'rating' => isset($game['rating']) ? round($game['rating']) . '%' : null,
                'plateforms' => collect($game['platforms'])->pluck('abbreviation')->implode(', '),
            ]);
        })->toArray();
    }
}
