<?php
// video_stream.php
set_time_limit(0);
$videoFile = isset($_GET['file']) ? urldecode($_GET['file']) : '';

if (!$videoFile || !file_exists($videoFile)) {
    header('HTTP/1.1 404 Not Found');
    exit('비디오 파일을 찾을 수 없습니다.');
}

// 파일 확장자에 따른 MIME 타입 설정
$fileExtension = strtolower(pathinfo($videoFile, PATHINFO_EXTENSION));
switch ($fileExtension) {
    case 'mp4':
        $mimeType = 'video/mp4';
        break;
    case 'avi':
        $mimeType = 'video/x-msvideo';
        break;
    case 'mov':
        $mimeType = 'video/quicktime';
        break;
    case 'mpeg':
    case 'mpg':
        $mimeType = 'video/mpeg';
        break;
    case 'webm':
        $mimeType = 'video/webm';
        break;
    case 'mkv':
        $mimeType = 'video/x-matroska';
        break;
    default:
        $mimeType = 'application/octet-stream'; // 기본값, 명시적으로 지원되지 않는 타입
}

$fileSize = filesize($videoFile); // 파일 크기

if (isset($_SERVER['HTTP_RANGE'])) {
    // HTTP Range 요청 처리
    list($param, $range) = explode('=', $_SERVER['HTTP_RANGE']);
    if (strtolower(trim($param)) != 'bytes') {
        header('HTTP/1.1 400 Invalid Request');
        exit;
    }
    list($from, $to) = explode('-', $range);
    if (!$to) {
        $to = $fileSize - 1;
    }

    $start = intval($from);
    $end = min(intval($to), $fileSize - 1);
    $length = $end - $start + 1;

    header("Content-Type: $mimeType");
    header("Content-Length: $length");
    header("Content-Range: bytes $start-$end/$fileSize");
    header("Accept-Ranges: bytes");
    header('HTTP/1.1 206 Partial Content');

    $file = fopen($videoFile, 'rb');
    fseek($file, $start); // 요청된 시작 위치로 파일 포인터 이동
    while (!feof($file) && ($pos = ftell($file)) <= $end) {
        echo fread($file, min(1024 * 16, $end - $pos + 1)); // 부분적으로 파일 읽기 및 출력
    }
    fclose($file);
} else {
    // 전체 파일 전송
    header("Content-Type: $mimeType");
    header("Content-Length: " . $fileSize);
    ob_end_flush(); // 출력 버퍼 비우기 및 비활성화
    readfile($videoFile);
}
?>