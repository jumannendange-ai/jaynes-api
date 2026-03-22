<?php

header("Content-Type: application/json; charset=UTF-8");

$api = "https://zimotv.com/mb/api/get-categories.php";

$data = json_decode(@file_get_contents($api), true);

if (!isset($data['categories'])) {

    echo json_encode([

        "success" => false,

        "message" => "Failed to load categories"

    ]);

    exit;

}

$categories = [];

foreach ($data['categories'] as $cat) {

    $name = trim($cat['name']);

    // ==========================

    // IMAGE FROM ZIMO

    // ==========================

    $image = $cat['image'] ?? $cat['icon'] ?? "";

    if ($image && strpos($image, "http") !== 0) {

        $image = "https://zimotv.com" . $image;

    }

    // ==========================

    // DEFAULT LINK

    // ==========================

    $link = "https://dde.ct.ws/face.php"; // <-- Updated Local Channels link

    // ==========================

    // CATEGORY → LINK MAPPING

    // ==========================

    if (stripos($name, "mechi") !== false) {

        $link = "https://dde.ct.ws/live.php";

    }

    elseif (preg_match('/local\s*server\s*2/i', $name)) {

        $link = "https://dde.ct.ws/local2.php";

    }

    elseif (stripos($name, "nbc") !== false) {

        $link = "https://dde.ct.ws/local.php";

    }

    elseif (stripos($name, "uefa") !== false || stripos($name, "champions") !== false) {

        $link = "https://dde.ct.ws/home.php";

    }

    $categories[] = [

        "id"    => $cat['id'] ?? null,

        "name"  => $name,

        "link"  => $link,

        "image" => $image

    ];

}

echo json_encode([

    "success" => true,

    "count" => count($categories),

    "categories" => $categories

], JSON_PRETTY_PRINT);