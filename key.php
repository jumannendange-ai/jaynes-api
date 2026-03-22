<?php

header("Content-Type: application/json");

// API URL

$apiUrl = "https://pixtvmax.quest/api/categories/1769090478198/channels";

// Fetch data

$json = @file_get_contents($apiUrl);

if ($json === false) {

    echo json_encode(["success" => false, "error" => "API not reachable"]);

    exit;

}

$data = json_decode($json, true);

$channels = [];

foreach ($data as $ch) {

    // chukua MPD tu

    if (!isset($ch['mpd_url']) || !str_contains($ch['mpd_url'], '.mpd')) {

        continue;

    }

    // ClearKey (kama ipo)

    $key = null;

    if (

        isset($ch['drm_type']) &&

        $ch['drm_type'] === "CLEARKEY" &&

        isset($ch['headers']['kid'], $ch['headers']['key'])

    ) {

        $key = $ch['headers']['kid'] . ":" . $ch['headers']['key'];

    }

    $channels[] = [

        "id"    => $ch['id'] ?? null,

        "name"  => $ch['name'] ?? null,

        "image" => $ch['logo_url'] ?? null,

        "url"   => $ch['mpd_url'],

        "key"   => $key,

        "drm"   => $ch['drm_type'] ?? "NONE"

    ];

}

// Output

echo json_encode([

    "success" => true,

    "count"   => count($channels),

    "channels"=> $channels

], JSON_PRETTY_PRINT);