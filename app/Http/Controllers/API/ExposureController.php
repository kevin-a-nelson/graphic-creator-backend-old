<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExposureController extends Controller
{

    public function fetchEventFromExposure($event_id)
    {
        $api_key = config('app.exposure_api_key');

        // Create timestamp
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone('UTC'));
        $timestamp = $datetime->format('Y-m-d\TH:i:s.u\Z');

        // Create hashString
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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $event = trim(curl_exec($ch));
        $event = json_decode($event);

        return $event;

    }

    public function getDivisionNames($event)
    {
        $games = $event->Games->Results;
        $divisionNames = [];
        foreach ($games as $game) {
            $pool = $game->Division->Name;
            $divisionNames[$pool] = true;
        }
        return array_keys($divisionNames);
    }

    public function getPools($event, $divisionName)
    {
        $pools = [];
        $games = $event->Games->Results;
        foreach ($games as $game) {

            if ($divisionName != $game->Division->Name) {
                continue;
            }

            // Home Team
            $poolName = $game->HomeTeam->PoolName;

            if (!array_key_exists($poolName, $pools)) {
                $pools[$poolName] = [];
            }

            if (!in_array($game->HomeTeam->Name, $pools[$poolName])) {
                array_push($pools[$poolName], $game->HomeTeam->Name);
            }

            // Away Team
            $poolName = $game->AwayTeam->PoolName;

            if (!array_key_exists($poolName, $pools)) {
                $pools[$poolName] = [];
            }

            if (!in_array($game->AwayTeam->Name, $pools[$poolName])) {
                array_push($pools[$poolName], $game->AwayTeam->Name);
            }
        }
        ksort($pools);
        return $pools;
    }

    public function getPoolNames($event, $divisionName)
    {
        $games = $event->Games->Results;
        $poolNames = [];
        foreach ($games as $game) {
            if ($game->Division->Name == $divisionName) {
                $poolNames[$game->HomeTeam->PoolName] = true;
                $poolNames[$game->AwayTeam->PoolName] = true;
            }
        }
        $poolNames = array_keys($poolNames);
        sort($poolNames);
        return $poolNames;
    }

    public function getDivisions($event)
    {
        $divisionNames = $this->getDivisionNames($event);
        $games = $event->Games->Results;
        $divisions = [];
        foreach ($divisionNames as $divisionName) {
            $pools = $this->getPools($event, $divisionName);
            $divisions[$divisionName] = ["Pools" => $pools];
        }
        ksort($divisions);
        return $divisions;
    }

    public function getEvent($event_id)
    {
        $event = $this->fetchEventFromExposure($event_id);
        $eventName = $event->Games->Results[0]->Division->Event->Name;
        $divisions = $this->getDivisions($event);
        return [
            "Name" => $eventName,
            "Divisions" => $divisions,
        ];
    }

    public function formattedEvent($event_id)
    {
        $event = $this->getEventGames($event_id);
        $eventGames = $event->Games->Results;
        return $eventGames;
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

    public function playoffGames(Request $request, int $event_id, int $chunk = -1)
    {
        $games = $this->getEventGames($event_id);
        $games = $this->getPlayoffGames($games);

        dd($games);
        // Each game has a number attribute
        // The Number tells us where the game is within a bracket
        $games = $this->orderGamesByNumber($games);

        if ($chunk == -1) {
            return $games;
        }

        // Each playoff has 3, 4 team brackets
        // the param $chunk is used to
        // query 1 of the 3 4 team brackets
        if ($chunk) {
            $games = array_chunk($games, 3);
            $games = $games[$chunk - 1];
        }

        return $games;
    }

}
