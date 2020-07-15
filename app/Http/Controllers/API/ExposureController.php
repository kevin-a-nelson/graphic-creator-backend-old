<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nesk\Puphpeteer\Puppeteer;

class ExposureController extends Controller
{

    public function screenShot(Request $request)
    {
        // $graphicCreatorUrl = "http://prepnetworkbrackets.surge.sh/";
        $graphicCreatorUrl = "http://localhost:3000/";
        $url = "{$graphicCreatorUrl}?event={$request->input('text')}&display={$request->input('display')}";
        $puppeteer = new Puppeteer;
        $browser = $puppeteer->launch(["defaultViewport" => ['width' => 1300, 'height' => 512]]);
        $page = $browser->newPage();
        $page->goto($url);
        $imageString = $page->screenshot([
            'encoding' => 'base64',
            'type' => 'png',
            'clip' => [
                'x' => 266,
                'y' => 0,
                'width' => 1024,
                'height' => 512,
            ],
        ]);
        $browser->close();
        return [
            "ImageString" => $imageString,
        ];
    }

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

    public function abbrTeamName($teamName)
    {
        $teamName = str_replace("Basketball", "BBall", $teamName);
        $teamName = str_replace("Academy", "ACAD", $teamName);
        $teamName = str_replace("17U", "", $teamName);
        $teamName = str_replace("16U", "", $teamName);
        $teamName = str_replace("15U", "", $teamName);
        $teamName = str_replace("14U", "", $teamName);
        $teamName = str_replace("13U", "", $teamName);
        $teamName = str_replace("12U", "", $teamName);
        $teamName = str_replace("11U", "", $teamName);
        $teamName = str_replace("10U", "", $teamName);
        $teamName = str_replace("17u", "", $teamName);
        $teamName = str_replace("15u", "", $teamName);
        $teamName = str_replace("16u", "", $teamName);
        $teamName = str_replace("14u", "", $teamName);
        $teamName = str_replace("13u", "", $teamName);
        $teamName = str_replace("12u", "", $teamName);
        $teamName = str_replace("11u", "", $teamName);
        $teamName = str_replace("10u", "", $teamName);
        $teamName = str_replace("Alabama", "AL", $teamName);
        $teamName = str_replace("Alaska", "AK", $teamName);
        $teamName = str_replace("American Samoa", "AS", $teamName);
        $teamName = str_replace("Arizona", "AZ", $teamName);
        $teamName = str_replace("Arkansas", "AR", $teamName);
        $teamName = str_replace("California", "CA", $teamName);
        $teamName = str_replace("Colorado", "CO", $teamName);
        $teamName = str_replace("Connecticut", "CT", $teamName);
        $teamName = str_replace("Delaware", "DE", $teamName);
        $teamName = str_replace("Dist. of Columbia", "DC", $teamName);
        $teamName = str_replace("Florida", "FL", $teamName);
        $teamName = str_replace("Georgia", "GA", $teamName);
        $teamName = str_replace("Guam", "GU", $teamName);
        $teamName = str_replace("Hawaii", "HI", $teamName);
        $teamName = str_replace("Idaho", "ID", $teamName);
        $teamName = str_replace("Illinois", "IL", $teamName);
        $teamName = str_replace("Indiana", "IN", $teamName);
        $teamName = str_replace("Iowa", "IA", $teamName);
        $teamName = str_replace("Kansas", "KS", $teamName);
        $teamName = str_replace("Kentucky", "KY", $teamName);
        $teamName = str_replace("Louisiana", "LA", $teamName);
        $teamName = str_replace("Maine", "ME", $teamName);
        $teamName = str_replace("Maryland", "MD", $teamName);
        $teamName = str_replace("Marshall Islands", "MH", $teamName);
        $teamName = str_replace("Massachusetts", "MA", $teamName);
        $teamName = str_replace("Michigan", "MI", $teamName);
        $teamName = str_replace("Micronesia", "FM", $teamName);
        $teamName = str_replace("Minnesota", "MN", $teamName);
        $teamName = str_replace("Mississippi", "MS", $teamName);
        $teamName = str_replace("Missouri", "MO", $teamName);
        $teamName = str_replace("Montana", "MT", $teamName);
        $teamName = str_replace("Nebraska", "NE", $teamName);
        $teamName = str_replace("New Hampshire", "NH", $teamName);
        $teamName = str_replace("New Jersey", "NJ", $teamName);
        $teamName = str_replace("New Mexico", "NM", $teamName);
        $teamName = str_replace("New York", "NY", $teamName);
        $teamName = str_replace("North Carolina", "NC", $teamName);
        $teamName = str_replace("North Dakota", "ND", $teamName);
        $teamName = str_replace("Northern Marianas", "MP", $teamName);
        $teamName = str_replace("Ohio", "OH", $teamName);
        $teamName = str_replace("Oklahoma", "OK", $teamName);
        $teamName = str_replace("Oregon", "OR", $teamName);
        $teamName = str_replace("Palau", "PW", $teamName);
        $teamName = str_replace("Pennsylvania", "PA", $teamName);
        $teamName = str_replace("Puerto Rico", "PR", $teamName);
        $teamName = str_replace("Rhode Island", "RI", $teamName);
        $teamName = str_replace("South Carolina", "SC", $teamName);
        $teamName = str_replace("South Dakota", "SD", $teamName);
        $teamName = str_replace("Tennessee", "TN", $teamName);
        $teamName = str_replace("Texas", "TX", $teamName);
        $teamName = str_replace("Utah", "UT", $teamName);
        $teamName = str_replace("Vermont", "VT", $teamName);
        $teamName = str_replace("Virginia", "VA", $teamName);
        $teamName = str_replace("Virgin Islands", "VI", $teamName);
        $teamName = str_replace("Washington", "WA", $teamName);
        $teamName = str_replace("West Virginia", "WV", $teamName);
        $teamName = str_replace("Wisconsin", "WI", $teamName);
        $teamName = str_replace("Wyoming", "WY", $teamName);
        return $teamName;
    }

    public function getTeamRecords($event)
    {
        $games = $event->Games->Results;
        $teams = [];
        foreach ($games as $game) {
            if (!array_key_exists($game->HomeTeam->TeamId, $teams)) {
                $teams[$game->HomeTeam->TeamId] = [
                    "Name" => $this->abbrTeamName($game->HomeTeam->Name),
                    "Wins" => 0,
                    "Loses" => 0,
                ];
            }
            if (!array_key_exists($game->AwayTeam->Name, $teams)) {
                $teams[$game->AwayTeam->TeamId] = [
                    "Name" => $this->abbrTeamName($game->AwayTeam->Name),
                    "Wins" => 0,
                    "Loses" => 0,
                ];
            }
            if ($game->HomeTeam->Score > $game->AwayTeam->Score) {
                $teams[$game->HomeTeam->TeamId]["Wins"] += 1;
                $teams[$game->AwayTeam->TeamId]["Loses"] += 1;
            } elseif ($game->HomeTeam->Score < $game->AwayTeam->Score) {
                $teams[$game->HomeTeam->TeamId]["Loses"] += 1;
                $teams[$game->AwayTeam->TeamId]["Wins"] += 1;
            }
        }
        return $teams;
    }

    public function getPools($event, $divisionName, $teamRecords)
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

            if (!in_array($teamRecords[$game->HomeTeam->TeamId], $pools[$poolName])) {
                array_push($pools[$poolName], $teamRecords[$game->HomeTeam->TeamId]);
            }

            // Away Team
            $poolName = $game->AwayTeam->PoolName;

            if (!array_key_exists($poolName, $pools)) {
                $pools[$poolName] = [];
            }

            if (!in_array($teamRecords[$game->AwayTeam->TeamId], $pools[$poolName])) {
                array_push($pools[$poolName], $teamRecords[$game->AwayTeam->TeamId]);
            }
        }
        ksort($pools);
        foreach ($pools as $pool => $teams) {
            usort($pools[$pool], function ($a, $b) {
                return $a["Wins"] < $b["Wins"];
            });
        }
        return $pools;
    }

    public function getDivisions($event)
    {
        $divisionNames = $this->getDivisionNames($event);
        $teamRecords = $this->getTeamRecords($event);
        $games = $event->Games->Results;
        $divisions = [];
        foreach ($divisionNames as $divisionName) {
            $pools = $this->getPools($event, $divisionName, $teamRecords);
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

}
