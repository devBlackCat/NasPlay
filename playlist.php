<section id="playlistSection">
    <h2 id="toggleButton">재생 목록 관리 -</h2>

    <ul id="playlistList"></ul>

    <script>
    // 페이지 로드 시 재생 목록 로드
    window.onload = function() {
        loadPlaylists();

        // 토글 버튼 설정
        const toggleButton = document.getElementById('toggleButton');
        const pageElement = document.querySelector('.page'); // 페이지 요소 선택

        toggleButton.onclick = function() {
            if (playlistList.style.display === 'none') {
                playlistList.style.display = 'block';
                toggleButton.textContent = '재생 목록 관리 -';
                playlistSection.classList.remove('hide');
                pageElement.style.width = 'calc(100% - 380px)'; // 재생 목록이 보일 때 페이지 너비 조정
            } else {
                playlistList.style.display = 'none';
                toggleButton.textContent = '+';
                playlistSection.classList.add('hide');
                pageElement.style.width = 'calc(100% - 40px)';// 재생 목록이 숨겨질 때 페이지 너비를 100%로 설정
            }
        };
    };

    // 재생 목록 항목 추가
    function addPlaylistItem(title, file) {
        const listItem = document.createElement('li');
        listItem.textContent = title;
        listItem.classList.add('playlist-item');
        listItem.dataset.file = file;

        const deleteBtn = document.createElement('span');
        deleteBtn.textContent = 'X';
        deleteBtn.classList.add('delete-btn');
        deleteBtn.onclick = function() {
            if (confirm('정말로 이 재생 목록을 삭제하시겠습니까?')) {
                fetch('playlist_delete.php?file=' + encodeURIComponent(listItem.dataset.file)+'&list=1')
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === 'success') {
                        alert('재생 목록이 성공적으로 삭제되었습니다.');
                        loadPlaylists(); // 재생 목록 삭제 후 새로운 재생 목록 로드
                    } else {
                        alert('재생 목록 삭제에 실패했습니다.');
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        };

        listItem.appendChild(deleteBtn);
        listItem.onclick = function() {
            if (!event.target.classList.contains('delete-btn')) {
                window.location.href = 'player.php?file=' + encodeURIComponent(listItem.dataset.file);
            }
        };

        document.getElementById('playlistList').appendChild(listItem);
    }

    // 서버에서 재생 목록 데이터 가져오기
    window.loadPlaylists = function() {
    const playlistList = document.getElementById('playlistList');
    playlistList.innerHTML = ''; // 재생 목록 초기화

    fetch('get_playlists.php')
    .then(response => response.json())
    .then(data => {
        data.forEach(playlist => {
            addPlaylistItem(playlist.title, playlist.file);
        });
    })
    .catch(error => console.error('Error:', error));
}
    </script>
</section>