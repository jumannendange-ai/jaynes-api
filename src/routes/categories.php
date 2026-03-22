<?php
/**
 * GET /categories
 * Inarudisha makundi ya channels kutoka Supabase
 */

requireMethod('GET');

$result = sb('/rest/v1/categories?select=id,name,image,description,link,channel_count&order=sort_order.asc,name.asc');

if (!$result['ok']) {
    // Rudisha fallback kama Supabase haifiki
    ok([
        'categories' => [
            ['id'=>'1','name'=>'NBC Premier League','image'=>'','description'=>'Mechi za NBC','link'=>'','channel_count'=>2],
            ['id'=>'2','name'=>'Azam Sports',       'image'=>'','description'=>'Azam Sports HD','link'=>'','channel_count'=>4],
            ['id'=>'3','name'=>'Habari',             'image'=>'','description'=>'Channels za habari','link'=>'','channel_count'=>5],
            ['id'=>'4','name'=>'Burudani',           'image'=>'','description'=>'Tamthiliya na muziki','link'=>'','channel_count'=>8],
            ['id'=>'5','name'=>'Watoto',             'image'=>'','description'=>'Channels za watoto','link'=>'','channel_count'=>3],
        ],
        'source' => 'fallback'
    ]);
}

$cats = $result['data'] ?? [];

// Sanitize
foreach ($cats as &$cat) {
    $cat['image']         = $cat['image']         ?? '';
    $cat['description']   = $cat['description']   ?? '';
    $cat['link']          = $cat['link']           ?? '';
    $cat['channel_count'] = $cat['channel_count']  ?? 0;
}

ok(['categories' => $cats, 'count' => count($cats)]);
