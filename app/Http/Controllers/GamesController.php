<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

class GamesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $before = Carbon::now()->subMonths(2)->timestamp;
        $current = Carbon::now()->timestamp;
        $after = Carbon::now()->addMonths(2)->timestamp;
        $afterFourMonths = Carbon::now()->addMonths(4)->timestamp;

        // $popularGames = Http::withHeaders(config('services.igdb'))
        //     ->withBody(
        //         "fields name, cover.url, first_release_date, total_rating_count, platforms.abbreviation, rating, slug;
        //     where platforms = (48,49,130,6)
        //     & (first_release_date >= {$before}
        //     & first_release_date < {$after})
        //     & total_rating_count > 5;
        //     sort total_rating_count desc;
        //     limit 12;",
        //         "text/plain"
        //     )->post('https://api.igdb.com/v4/games')
        //     ->json();


        // dump($popularGames);

        // $recentlyReviewed = Http::withHeaders(config('services.igdb'))
        //     ->withBody(
        //         "fields name, cover.url, first_release_date, total_rating_count, platforms.abbreviation, rating, rating_count, summary, slug;
        //     where platforms = (48,49,130,6)
        //     & (first_release_date >= {$before}
        //     & first_release_date < {$current}
        //     & total_rating_count > 5);
        //     sort rating_count desc;
        //     limit 4;
        //     ",
        //         "text/plain"
        //     )->post('https://api.igdb.com/v4/games')
        //     ->json();


        // dump($recentlyReviewed);

        // $mostAnticipated = Http::withHeaders(config('services.igdb'))
        // ->withBody(
        //     "fields name, cover.url, first_release_date, total_rating_count, platforms.abbreviation, rating, rating_count, summary, slug;
        // where platforms = (48,49,130,6)
        // & (first_release_date >= {$current}
        // & first_release_date < {$afterFourMonths}
        // & total_rating_count > 5);
        // sort rating_count desc;
        // limit 4;
        // ",
        //     "text/plain"
        // )->post('https://api.igdb.com/v4/games')
        // ->json();

        // dump($mostAnticipated);

        // $comingSoon = Http::withHeaders(config('services.igdb'))
        // ->withBody(
        //     "fields name, cover.url, first_release_date, total_rating_count, platforms.abbreviation, rating, rating_count, summary, slug;
        // where platforms = (48,49,130,6)
        // & first_release_date < {$current}
        // & total_rating_count > 5);
        // sort first_release_date desc;
        // limit 4;
        // ",
        //     "text/plain"
        // )->post('https://api.igdb.com/v4/games')
        // ->json();

        // dump($comingSoon);

        return view('index', [
            // 'popularGames' => $popularGames,
            // 'recentlyReviewed' => $recentlyReviewed,
            // 'mostAnticipated' => $mostAnticipated,
            // 'comingSoon' => $comingSoon
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $game = Http::withHeaders(config('services.igdb'))
            ->withOptions([
                'body' => "
                fields name, cover.*, first_release_date, platforms.abbreviation, rating, slug, summary,
                involved_companies.company.name, genres.*, aggregated_rating, websites.*, videos.*, screenshots.*,
                similar_games.cover.url, similar_games.name, similar_games.rating, similar_games.platforms.abbreviation, similar_games.slug;
                where slug = \"{$slug}\";
            "
            ])
            ->post('https://api.igdb.com/v4/games')
            ->json();

        return view('show', [
            'game' => $game[0],
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
