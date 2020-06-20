<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExposureController extends Controller
{
    public function getEventGames($event_id)
    {
        $api_key = config('app.exposure_api_key');
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone('UTC'));
        $timestamp = $datetime->format('Y-m-d\TH:i:s.u\Z');

        $path = $api_key . '&get&' . $timestamp . '&/api/v1/games';
        $message = strtoupper($path);
        $secret_key = config('app.exposure_secret_key');
        $hash = hash_hmac('sha256', $message, $secret_key, true);
        $hashString = base64_encode($hash);

        $headers = [
            'Timestamp:' . $timestamp,
            'Authentication:' . $api_key . '.' . $hashString,
            "Content-Type: application/json",
            'Accept: application/json',
            'Content-length: 0',
        ];

        $url = "https://basketball.exposureevents.com/api/v1/games?eventid=" . $event_id;

        // dd($url, $headers);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $games = trim(curl_exec($ch));

        $games = json_decode($games);

        return $games->Games->Results;
    }

    public function games(Request $request, $event_id)
    {
        return $this->getEventGames($event_id);
    }

    public function getPlayoffGames($games)
    {
        $isPlayoffGame = function ($team) {

            // If Home and Away teams both don't have ExternalBracketId's then it is not a playoff game
            if ((!property_exists($team->HomeTeam, 'ExternalBracketId') &&
                (!property_exists($team->AwayTeam, 'ExternalBracketId')))) {
                return false;
            }

            // If ExternalId of HomeTeam is < 1 it is a losers bracket game and not a playoff game
            if ((property_exists($team->HomeTeam, 'ExternalBracketId')) &&
                ($team->HomeTeam->ExternalBracketId < 1)) {
                return false;
            }

            // If ExternalId of Away is < 1 it it is a losers bracket game and not a playoff game
            if ((property_exists($team->AwayTeam, 'ExternalBracketId')) &&
                ($team->AwayTeam->ExternalBracketId < 1)) {
                return false;
            }

            return true;
        };

        return array_filter($games, $isPlayoffGame);
    }

    public function orderGamesByNumber($games)
    {
        // Number is the order in which the games are played
        $externalIdAsc = function ($game1, $game2) {
            return $game1->Number > $game2->Number;
        };

        usort($games, $externalIdAsc);

        return $games;
    }

    public function playoffGames(Request $request, int $event_id)
    {
        $games = $this->getEventGames($event_id);
        $games = $this->getPlayoffGames($games);

        // Each game has a number attribute
        // The Number tells us where the game is within a bracket
        $games = $this->orderGamesByNumber($games);

        // Each playoff has 3, 4 team brackets
        // the param $request->bracket is used to
        // query 1 of the 3 4 team brackets
        if ($request->bracket) {
            $games = array_chunk($games, 3);
            $games = $games[$request->bracket - 1];
        }

        return $games;
    }

}
