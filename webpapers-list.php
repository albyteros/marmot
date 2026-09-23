<?php
/**
 * Lists every webpaper in the Webpapers submodule as JSON.
 * A webpaper is any folder in Webpapers/ with an index.html.
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache');

$papers = [];
foreach (glob(__DIR__ . '/Webpapers/*/index.html') as $file) {
    $dir   = basename(dirname($file));
    $title = $dir;
    if (preg_match('/<title>(.*?)<\/title>/is', file_get_contents($file, false, null, 0, 4096), $m)) {
        $title = trim(html_entity_decode($m[1]));
    }
    $papers[] = [
        'name'  => $title,
        'url'   => '/Webpapers/' . rawurlencode($dir) . '/',
    ];
}

echo json_encode($papers);
