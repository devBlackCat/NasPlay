<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>비디오 파일 업로드 및 재생 목록 추가</title>
    <link rel="stylesheet" href="style.css">

    <script src="/Sortable.min.js"></script>
</head>
<body>
    <section class="page">
        <h2>비디오 파일 업로드 및 재생 목록 추가</h2>
        <form id="playlistForm" method="post" action="create_playlist.php">
            <label for="directoryInput">디렉토리:</label>
            <input type="text" id="directoryInput" name="directory" placeholder="예: Z:\data\개인\~~~ " required><br><br>

            <label for="fileInput">파일 선택:</label>
            <input type="file" id="fileInput" multiple accept=".mp4,.avi,.mkv,.mov"><br><br>

            <ul id="fileList"></ul>

            <label for="playlistTitle">재생 목록 제목:</label>
            <input type="text" id="playlistTitle" name="title" placeholder="재생 목록 제목 입력" required><br><br>

            <input type="hidden" name="fileList" id="hiddenFileList">

            <button type="button" id="addPlaylist">재생 목록 추가</button>
        </form>
    </section>
<?
/* playlist.php import */
include 'playlist.php';
?>
<script>
new Sortable(document.getElementById('fileList'), {
    animation: 150, // 드래그 중인 항목의 애니메이션 속도
    ghostClass: 'sortable-ghost', // 드래그 중인 항목에 적용될 CSS 클래스
});
    
document.getElementById('fileInput').addEventListener('change', function(e) {
    let directory = document.getElementById('directoryInput').value;
    const baseDirectory = 'Z:\\data\\개인\\';

    if (!directory) {
        alert('디렉토리를 입력해주세요.');
        e.target.value = ''; // 파일 입력 필드 초기화
        return;
    }

    if (!directory.endsWith('\\')) {
        directory += '\\';
    }

    // baseDirectory와 일치하는지 검증
    if (!directory.startsWith(baseDirectory)) {
        alert('현재 디렉토리의 하위 파일만 재생 가능합니다.');
        e.target.value = ''; // 파일 입력 필드 초기화
        return;
    }

    const fileList = document.getElementById('fileList');
    fileList.innerHTML = ''; // 리스트 초기화

    Array.from(e.target.files).forEach(file => {
        // 'Z:\data\개인\'을 '/'로 대체하고 나머지 '\'를 '/'로 변경
        let relativePath = directory.replace(new RegExp('\\\\', 'g'), '/') + file.name;
        relativePath = relativePath.replace(baseDirectory.replace(new RegExp('\\\\', 'g'), '/'), '');

        const listItem = document.createElement('li');
        listItem.textContent = '' + relativePath; // 절대 경로를 상대 경로로 표시
        listItem.classList.add('file-list-item');

        const deleteBtn = document.createElement('span');
        deleteBtn.textContent = 'X';
        deleteBtn.classList.add('file-delete-btn');
        deleteBtn.onclick = function() { listItem.remove(); };

        listItem.appendChild(deleteBtn);
        fileList.appendChild(listItem);
    });

        // 파일 업로드 후 디렉토리 입력 필드 초기화
        document.getElementById('directoryInput').value = '';
    });

    document.getElementById('addPlaylist').addEventListener('click', function() {
    const fileListItems = document.querySelectorAll('#fileList .file-list-item');
    const files = [];

    // 각 파일 목록 아이템을 순회하며 파일 경로만을 추출
    fileListItems.forEach(item => {
        // item의 텍스트 콘텐츠에서 'X' 버튼 텍스트를 제외하고 파일 경로만 추출
        // 이를 위해 <span> 태그를 제거하고 순수한 텍스트만을 가져옵니다.
        const filePath = item.cloneNode(true); // 깊은 복사를 통해 <li> 요소를 복제
        const deleteBtn = filePath.querySelector('.file-delete-btn'); // <span> 요소를 찾음
        if (deleteBtn) deleteBtn.remove(); // <span> 요소가 있으면 삭제
        files.push(filePath.textContent.trim()); // 최종적으로 정제된 파일 경로를 배열에 추가
    });

    if (files.length === 0) {
        alert('파일을 선택해주세요.');
        return;
    }

    const formData = new FormData();
    formData.append('title', document.getElementById('playlistTitle').value);
    formData.append('fileList', JSON.stringify(files));

    fetch('create_playlist.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === 'success') {
            alert('재생 목록이 성공적으로 추가되었습니다.');
            loadPlaylists(); // 재생 목록 추가 후 새로운 재생 목록 로드
        } else {
            alert('재생 목록 추가에 실패했습니다.');
        }
    })
    .catch(error => console.error('Error:', error));

    new Sortable(fileList, {
        animation: 150,
        ghostClass: 'blue-background-class'
    });
});
</script>

</body>
</html>

<?

?>