<?php
/* playlist_delete.php */
$file = $_GET['file'];

/* 현재 디렉토리에서  /playlists를 붙이고 그뒤에 $file을 붙인 디렉토리 파일을 삭제 */
if (strpos($file, 'playlists/') === 0 && unlink($file)) {
    echo 'success';
} else {
    echo 'fail';
}
?>