<?php
require_once('config.php');

$accounts = [
    ['name' => 'XXX', 'account_id' => INSTA_XXX_ACCOUNT_ID, 'access_token' => INSTA_XXX_ACCESS_TOKEN, 'url' => 'instagram_page_url'],
    ['name' => 'YYY' , 'account_id' => INSTA_YYY_ACCOUNT_ID, 'access_token' => INSTA_YYY_ACCESS_TOKEN, 'url' => 'instagram_page_url']
];
$graph_base_url = 'https://graph.facebook.com/v9.0/';
$limit = 30;
$fields = 'media_url,thumbnail_url,permalink';
$dir = '..' . DS . 'assets' . DS . 'json' . DS . 'instagram' . DS;

foreach ($accounts as $acc) {
    $params = [
        'pretty' => 1,
        'limit' => $limit,
        'fields' => $fields,
        'access_token' => $acc['access_token']
    ];
    $url = $graph_base_url . '/' . $acc['account_id'] . '/media' . '?' . http_build_query($params);

    // create a new cURL resource
    $ch = curl_init();
    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    // grab URL and get data from api
    $response = curl_exec($ch);
    // close cURL resource, and free up system resources
    curl_close($ch);

    $toArr = json_decode($response, true);
    $data = $toArr['data'];
    $posts = [];
    foreach ($data as $item) {
        if ($item['media_url']) {
            $img = $item['media_url'];
        } else if ($item['thumbnail_url']) { // media type = video
            $img = $item['thumbnail_url'];
        }
        $post = [
            'img' => $img,
            'link' => $item['permalink']
        ];
        array_push($posts, $post);
    }
    $result = [
        'account' => [
            'page' => $acc['url']
        ],
        'posts' => $posts
    ];

    $toJson = json_encode($result, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    $file_name = $acc['name'].'.json';
    if (isset($argv[1])) {
        $path = rtrim($argv[1], DS).DS; // check if path has '/' in the end position or not
        file_put_contents($path.$file_name, $toJson);
    } else {
        file_put_contents($dir.$file_name, $toJson);
    }
}
