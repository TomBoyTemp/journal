document.addEventListener('DOMContentLoaded', function() {
    const issueHeader = document.getElementById('issue-header');
    const articlesList = document.getElementById('articles-list');
    
    // Получаем ID выпуска из URL
    const urlParams = new URLSearchParams(window.location.search);
    const issueId = urlParams.get('id');
    
    if (!issueId) {
        showError('ID выпуска не указан');
        return;
    }
    
    // Загрузка информации о выпуске и статьях
    fetch(`/api/user/get_issue_article.php?id=${issueId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сети');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                renderIssueHeader(data.issue);
                renderArticles(data.articles);
            } else {
                showError(data.message || 'Не удалось загрузить данные выпуска');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showError('Произошла ошибка при загрузке данных');
        });
    
    function renderIssueHeader(issue) {
        issueHeader.innerHTML = `
            <h1 class="issue-title">Том ${issue.volume}, №${issue.issue_number} (${issue.year})</h1>
            <div class="issue-date">
                ${formatPublicationDate(issue.publication_date)}
            </div>
        `;
    }
    
    function renderArticles(articles) {
        if (articles.length === 0) {
            articlesList.innerHTML = '<div class="empty-message">В этом выпуске пока нет статей</div>';
            return;
        }
        
        articlesList.innerHTML = '';
        
        articles.forEach(article => {
            const articleLink = document.createElement('a');
            articleLink.href = `information_articles.php?id=${article.id}`;
            articleLink.className = 'article-card';
            
            const title = document.createElement('div');
            title.className = 'article-title';
            title.textContent = article.title;
            
            const authors = document.createElement('div');
            authors.className = 'article-authors';
            authors.textContent = article.authors_list || 'Авторы не указаны';
            
            const keywordsContainer = document.createElement('div');
            keywordsContainer.className = 'article-keywords';
            
            if (article.keywords && article.keywords.length > 0) {
                article.keywords.forEach(keyword => {
                    const keywordEl = document.createElement('span');
                    keywordEl.className = 'keyword';
                    keywordEl.textContent = keyword;
                    keywordsContainer.appendChild(keywordEl);
                });
            } else {
                keywordsContainer.textContent = 'Ключевые слова не указаны';
                keywordsContainer.style.color = '#999';
                keywordsContainer.style.fontStyle = 'italic';
            }
            
            articleLink.appendChild(title);
            articleLink.appendChild(authors);
            articleLink.appendChild(keywordsContainer);
            articlesList.appendChild(articleLink);
        });
    }
    
    function formatPublicationDate(dateString) {
        if (!dateString) return 'Дата публикации не указана';
        
        const date = new Date(dateString);
        return `Опубликован: ${date.toLocaleDateString()}`;
    }
    
    function showError(message) {
        issueHeader.innerHTML = '';
        articlesList.innerHTML = `<div class="error-message">${message}</div>`;
    }
});