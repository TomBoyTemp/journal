    function renderArticles(articles) {
        const articlesList = document.getElementById('searchResults');

        if (articles.length === 0) {
            articlesList.innerHTML = '<div class="empty-message">Статей не найдено</div>';
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
            }
            
            articleLink.appendChild(title);
            articleLink.appendChild(authors);
            articleLink.appendChild(keywordsContainer);
            articlesList.appendChild(articleLink);
        });
    }
    
    async function loadManuscripts(searchParams = '') {
    try {
        const response = await fetch(`/api/user/search_articles.php?query=${encodeURIComponent(searchParams)}`);
        const data = await response.json();
        
        const resultsContainer = document.getElementById('searchResults');
        if (data.status === 'success') {
            renderArticles(data.data);
        } else {
            resultsContainer.innerHTML = `<p class="error">${data.message}</p>`;
        }
    } catch (error) {
        console.error('Ошибка:', error);
    }
    }

document.getElementById('searchForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const query = formData.get('query');
    
    loadManuscripts(query);
});

document.addEventListener('DOMContentLoaded', function() {
    loadManuscripts();
});