<?php

// create_playlist.php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['title']) && !empty($_POST['fileList'])) {
    $directory = 'playlists/';
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $title = $_POST['title'];
    $files = json_decode($_POST['fileList'], true);
    $fileCount = count(glob($directory . "*.json")) + 1;
    $fileName = $directory . "list_" . str_pad($fileCount, 2, "0", STR_PAD_LEFT) . ".json";

    $playlist = [
        'title' => $title,
        'list' => $files
    ];

    // JSON_UNESCAPED_UNICODE 옵션을 사용하여 한글 인코딩 문제 해결
    file_put_contents($fileName, json_encode($playlist, JSON_UNESCAPED_UNICODE));

    echo "success";
    /* 2초뒤 index.php로 이동 */
    header('Refresh: 2; URL=index.php');
} else {
    echo "fail";
}

?>
