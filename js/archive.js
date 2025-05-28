document.addEventListener('DOMContentLoaded', function() {
    const archiveContainer = document.getElementById('issues-archive');
    
    // Загрузка опубликованных выпусков
    fetch('/api/user/get_published_issues.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                renderIssues(data.data);
            } else {
                showError(data.message || 'Не удалось загрузить архив выпусков');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showError('Произошла ошибка при загрузке архива');
        });
    
    function renderIssues(issues) {
        if (issues.length === 0) {
            archiveContainer.innerHTML = '<div class="empty-message">Нет опубликованных выпусков</div>';
            return;
        }
        
        archiveContainer.innerHTML = '';
        
        issues.forEach(issue => {
            const issueCard = document.createElement('div');
            issueCard.className = 'issue-card';
            
            const issueLink = document.createElement('a');
            issueLink.href = `/issue.php?id=${issue.id}`;
            
            const title = document.createElement('h2');
            title.className = 'issue-title';
            title.textContent = `Том ${issue.volume}, №${issue.issue_number} (${issue.year})`;
            
            const date = document.createElement('div');
            date.className = 'issue-date';
            
            // Форматируем дату публикации
            if (issue.publication_date) {
                const pubDate = new Date(issue.publication_date);
                date.textContent = `Опубликован: ${pubDate.toLocaleDateString()}`;
            } else {
                date.textContent = 'Дата публикации не указана';
            }
            
            issueLink.appendChild(title);
            issueLink.appendChild(date);
            issueCard.appendChild(issueLink);
            archiveContainer.appendChild(issueCard);
        });
    }
    
    function showError(message) {
        archiveContainer.innerHTML = `<div class="error-message">${message}</div>`;
    }
});