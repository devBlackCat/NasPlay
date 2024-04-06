<?php


// player.php
$playlistFile = isset($_GET['file']) ? $_GET['file'] : '';

// 재생 목록 파일 로드
if ($playlistFile && file_exists($playlistFile)) {
    $playlist = json_decode(file_get_contents($playlistFile));
} else {
    die("재생 목록 파일을 찾을 수 없습니다.");
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($playlist->title); ?> - 비디오 재생</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

   
    <div id="playlistContainer" class="playlist-container">
        <div id="playlistHeader" class="playlist-header">
            <img src="/img/icon01.png" alt="반복" class="icon repeat-icon" onclick="toggleLoop()">
           <!-- <img src="/img/icon02.png" alt="설정" class="icon settings-icon"> -->
            <img src="/img/icon03.png" alt="닫기" class="icon close-icon" onclick="togglePlaylist()">
        </div>
        <ul id="playlist" class="playlist">
            <!-- 재생 목록 아이템이 동적으로 여기에 추가될 것임 -->
        </ul>
    </div>

    <video id="videoPlayer" width="720" controls autoplay></video>

<script>

let isLoopActive = false;
let currentIndex = 0;
let isPausedByUser = false;
let isEnded = false;
let play_list = <?php echo json_encode($playlist->list, JSON_UNESCAPED_UNICODE); ?>;
function togglePlaylist() {
    const playlistContainer = document.getElementById('playlistContainer');
    const playlistHeaderImages = document.querySelectorAll('#playlistHeader img');
    const closeIcon = document.querySelector('.close-icon');

    if (playlistContainer.classList.contains('closed')) {
        playlistContainer.classList.remove('closed');
        playlistHeaderImages.forEach(img => img.style.display = 'block');
        closeIcon.src = "/img/icon03.png";
    } else {
        playlistContainer.classList.add('closed');
        playlistHeaderImages.forEach(img => {
            if (!img.classList.contains('close-icon')) {
                img.style.display = 'none';
            }
        });
        closeIcon.src = "/img/icon02.png";
    }
}

function toggleLoop() {
    isLoopActive = !isLoopActive;
    const repeatIcon = document.querySelector('.repeat-icon');
    repeatIcon.style.opacity = isLoopActive ? '1.0' : '0.5';
    const url = new URL(window.location.href);
    if (isLoopActive) {
        url.searchParams.set('loop', '1');
    } else {
        url.searchParams.delete('loop');
    }
    history.pushState(null, '', url.toString());
}

function playVideo(index) {
    
    const player = document.getElementById('videoPlayer');
    console.log("변환 전 파일 경로: ", play_list[index]);
    const videoPath = encodeURIComponent(play_list[index]);
    player.src = `video_stream.php?file=${videoPath}`;
    player.load();
    player.play();
    currentIndex = index;
    updateActiveItem(index);
}

function updateActiveItem(index) {
    document.querySelectorAll('.playlist-item').forEach((item, idx) => {
            item.classList.remove('active');
            if (idx === index) {
                item.classList.add('active');
            }
        });
}


function playNextVideo() {

    if (currentIndex < play_list.length - 1) { //currentIndex 값 즉, 현재 재생 중인 비디오의 인덱스 값이 재생 목록의 마지막 인덱스 값보다 작을 때
        playVideo(++currentIndex);
    }  
    else if (isLoopActive) {
        currentIndex = 0;
        playVideo(currentIndex);
        } 
        else {
        console.log("재생 목록의 끝에 도달했습니다.");
        if (isLoopActive) {
            console.log("반복 모드 활성화, 처음부터 재생합니다.");
            currentIndex = 0;
            playVideo(currentIndex);
        }
        }
    }

    function updateSessionPin() {
        //도메인의 file 값
        let domain = new URL(window.location.href).searchParams.get('file');
        const currentListIndex = currentIndex; // 현재 재생 중인 영상 인덱스
        const currentTime = videoPlayer.currentTime; // 현재 영상의 재생 시간
        

        // 세션 스토리지에 저장할 객체 생성
        const sessionPinData = {
            file: domain,
            list: currentListIndex.toString(),
            time: currentTime.toString()
        };

        // 세션 스토리지에 데이터 저장
        sessionStorage.setItem('sessionPin', JSON.stringify(sessionPinData));
}


document.addEventListener('DOMContentLoaded', function() {

        const urlParams = new URLSearchParams(window.location.search);
        const listIndex = parseInt(urlParams.get('list')) || 0;
        let itemsHtml = '';
        const playlistItems = document.querySelectorAll('.playlist-item');
        const repeatIcon = document.querySelector('.repeat-icon');
        let playlist = document.getElementById('playlist');
        const videoPlayer = document.getElementById('videoPlayer');
        let updateTimer;
        let sessionPin = sessionStorage.getItem('sessionPin'); 
        sessionPin = sessionPin ? JSON.parse(sessionPin) : {}; 


        for (let i = 0; i < <?php echo count($playlist -> list); ?> ; i++) {
            const isActive = listIndex == (i + 1) ? 'active' : '';
            itemsHtml += `<li class="playlist-item ${isActive}" onclick="playVideo(${i})"><?php echo htmlspecialchars($playlist->title); ?> ${i+1}</li>`;
        }
        playlist.innerHTML = itemsHtml;
        playlistItems.forEach((item, index) => {
            item.addEventListener('click', () => {
                playlistItems.forEach(item => item.classList.remove('active'));
                item.classList.add('active');
            });

            isLoopActive = urlParams.get('loop') === '1';
            repeatIcon.style.opacity = isLoopActive ? '1.0' : '0.5';
            repeatIcon.onclick = toggleLoop;
        });
        togglePlaylist();

        if (listIndex && listIndex - 1 < playlistItems.length) {
            playVideo(listIndex - 1);
        }else {
            playVideo(0); // 기본값으로 첫 번째 비디오를 재생합니다.
      

        }

        videoPlayer.addEventListener('pause', () => {
            isPausedByUser = true;
            console.log('영상이 일시정지되었습니다.');
        });

        videoPlayer.addEventListener('ended', () => {
            isEnded = true;
            console.log('영상이 모두 끝났습니다.');
            playNextVideo();
        });

        videoPlayer.addEventListener('error', (e) => {
            if (!isPausedByUser && !isEnded) {
                console.log('비디오 재생 중 오류 발생, 재시도를 시도합니다.');
                setTimeout(() => playVideo(currentIndex), 3000);
                setTimeout(() => {
                    if (!isPausedByUser && !isEnded) {
                        console.log('에러가 지속되어 다음 영상으로 넘어갑니다.');
                        playNextVideo();
                    }
                }, 30000);
            }
        });


        
        if (sessionPin.list !== undefined) {
            currentIndex = parseInt(sessionPin.list, 10); // 세션에서 가져온 currentIndex를 현재 인덱스로 설정
            playVideo(currentIndex); // 해당 비디오 재생
        } else {
            // 세션 스토리지에 저장된 currentIndex가 없는 경우, 첫 번째 비디오를 재생
            playVideo(0);
        }

            videoPlayer.addEventListener('play', updateSessionPin);
            videoPlayer.addEventListener('pause', updateSessionPin);
            videoPlayer.addEventListener('ended', updateSessionPin);

            // 2분(120000밀리초)마다 세션 업데이트
            clearInterval(updateTimer); // 기존 타이머가 있다면 초기화
            updateTimer = setInterval(updateSessionPin, 120000);

            // 페이지를 떠날 때, 정리 작업을 수행
            window.addEventListener('beforeunload', () => {
                clearInterval(updateTimer); // 타이머 정리
                updateSessionPin(); // 마지막 상태 저장
            });


});





</script>
</body>
</html>

<style>
.active {
    background-color: #0b5ed7;
    color: white;
}
    video{
        height: 100vh;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        width: auto;
    }

    body {
    margin: 0;
    background-color: #000;
    color: white;
    font-family: Arial, sans-serif;
}

.videoPlayer {
    width: 100%;
    height: auto;
}

.playlist-container {
    position: fixed;
    top: 0;
    right: 0;
    width: 250px;
    height: 100%;
    background: #333;
    color: black;
    overflow-y: auto;
    z-index: 100;
    transition: width 0.3s ease;
}
.playlist-container.closed {
    width: fit-content;
}
.playlist-container.closed .playlist,
.playlist-container.closed .playlist-header img:not(.close-icon) {
    display: none; /* 닫힌 상태에서는 모든 하위 요소를 숨김 */
}

.playlist-header {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    padding: 10px;
}

.icon {
    width: 24px;
    height: 24px;
    margin-left: 10px;
    cursor: pointer;
}

.repeat-icon, .settings-icon {
    display: none; /* 현재 기능이 구현되지 않아 숨김 처리 */
}

.playlist {
    list-style: none;
    padding: 0;
    margin: 0;
    width: 90%;
    margin-left: 5%;
}

.playlist-item {
    padding: 10px;
    border-bottom: 1px solid #555;
    cursor: pointer;
}

.playlist-item:hover {
    background-color: #0b5ed7;
    color: white;
}

/* 닫기 아이콘 클릭 시에 대한 스타일링 */
.playlist-container.closed {
    width: 50px;
}

.playlist-container.closed .playlist {
    display: none;
}

.playlist-container.closed .close-icon {
    transform: rotate(180deg);
}



</style>
