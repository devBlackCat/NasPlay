<?php
// get_playlists.php
$files = glob('playlists/*.json');
$playlists = array();

foreach ($files as $file) {
    $playlist = json_decode(file_get_contents($file));
    $playlists[] = array('title' => $playlist->title, 'file' => $file);
}

header('Content-Type: application/json');
echo json_encode($playlists);
?>