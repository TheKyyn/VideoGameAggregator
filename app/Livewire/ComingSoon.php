<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class ComingSoon extends Component
{
    public $comingSoon = [];

    public function loadComingSoon()
    {
        $current = Carbon::now()->timestamp;

        $comingSoon = Http::withHeaders(config('services.igdb'))
        ->withBody(
            "fields name, cover.url, first_release_date, total_rating_count, platforms.abbreviation, rating, rating_count, summary, slug;
        where platforms = (48,49,130,6)
        & first_release_date < {$current}
        & total_rating_count > 5);
        sort first_release_date desc;
        limit 4;
        ",
            "text/plain"
        )->post('https://api.igdb.com/v4/games')
        ->json();
    }

    public function render()
    {
        return view('livewire.coming-soon');
    }
}
