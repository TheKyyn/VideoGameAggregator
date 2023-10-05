<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
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

        $popularGames = Http::withHeaders(config('services.igdb'))
            ->withBody(
                "fields name, cover.url, first_release_date, total_rating_count, platforms.abbreviation, rating, slug;
            where platforms = (48,49,130,6)
            & (first_release_date >= {$before}
            & first_release_date < {$after})
            & total_rating_count > 5;
            sort total_rating_count desc;
            limit 12;",
                "text/plain"
            )->post('https://api.igdb.com/v4/games')
            ->json();


        $recentlyReviewed = Http::withHeaders(config('services.igdb'))
            ->withBody(
                "fields name, cover.url, first_release_date, total_rating_count, platforms.abbreviation, rating, rating_count, summary, slug;
            where platforms = (48,49,130,6)
            & (first_release_date >= {$before}
            & first_release_date < {$current}
            & total_rating_count > 5);
            sort rating_count desc;
            limit 4;
            ",
                "text/plain"
            )->post('https://api.igdb.com/v4/games')
            ->json();


        // dump($recentlyReviewed);

        $mostAnticipated = Http::withHeaders(config('services.igdb'))
        ->withBody(
            "fields name, cover.url, first_release_date, total_rating_count, platforms.abbreviation, rating, rating_count, summary, slug;
        where platforms = (48,49,130,6)
        & (first_release_date >= {$current}
        & first_release_date < {$afterFourMonths}
        & total_rating_count > 5);
        sort rating_count desc;
        limit 4;
        ",
            "text/plain"
        )->post('https://api.igdb.com/v4/games')
        ->json();

        // dump($mostAnticipated);

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

        // dump($comingSoon);

        return view('index', [
            'popularGames' => $popularGames,
            'recentlyReviewed' => $recentlyReviewed,
            'mostAnticipated' => $mostAnticipated,
            'comingSoon' => $comingSoon
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
            ->withBody([
                'body' => "
                fields *, cover.*, first_release_date, platforms.abbreviation, rating, slug, summary,
                involved_companies.company.name, genres.*, aggregated_rating, websites.*, videos.*, screenshots.*,
                similar_games.cover.url, similar_games.name, similar_games.rating, similar_games.platforms.abbreviation, similar_games.slug;
                where slug = \"{$slug}\";
            "
            ])
            ->post('https://api.igdb.com/v4/games')
            ->json();

        return view('show', [
            'game' => $this->formatGameForView($game[0]),
        ]);
    }

    private function formatGameForView($game)
    {
        $temp = collect($game)->merge([
            'coverImageUrl' => Str::replaceFirst('thumb', 'cover_big', $game['cover']['url']),
            'genres' => collect($game['genres'])->pluck('name')->implode(', '),
            'involvedCompanies' => isset($game['involved_companies']) ? $game['involved_companies'][0]['company']['name'] : null,
            'platforms' => collect($game['platforms'])->pluck('abbreviation')->implode(', '),
            'memberRating' => array_key_exists('rating', $game) ? round($game['rating']) . '%' : '0%',
            'criticRating' => array_key_exists('aggregated_rating', $game) ? round($game['aggregated_rating']) . '%' : '0%',
            'trailer' => 'https://youtube.com/watch/'.$game['video'][0]['video_id'],
            'screenshots' => collect($game['screenshots'])->map(function ($screenshot) {
                return [
                    'huge' => Str::replaceFirst('thumb', 'screenshot_huge', $screenshot['url']),
                    'big' => Str::replaceFirst('thumb', 'screenshot_big', $screenshot['url']),
                ];
            })->take(9),
            'similarGames' => collect($game['similar_games'])->map(function ($game) {
                return collect($game)->merge([
                    'coverImageUrl' => array_key_exists('cover', $game)
                    ? Str::replaceFirst('thumb', 'cover_big', $game['cover']['url'])
                    : 'https://via.placeholder.com/264x352',
                    'rating' => isset($game['rating']) ? round($game['rating']).'%' : null,
                    'plateforms' => array_key_exists('platforms', $game)
                    ? collect($game['platforms'])->pluck('abbreviation')->implode(', ')
                    : null,
                ]);
            })->take(6),
            'social' => [
                'website' => collect($game['websites'])->first(),
                'facebook' => collect($game['websites'])->filter(fn ($website) => Str::contains($website['url'], 'facebook'))->first(),
                'twitter' => collect($game['websites'])->filter(fn ($website) => Str::contains($website['url'], 'twitter'))->first(),
                'instagram' => collect($game['websites'])->filter(fn ($website) => Str::contains($website['url'], 'instagram'))->first(),
            ]

        ]);

        return $temp;
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
