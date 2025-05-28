// Функция для преобразования статуса в читаемый формат
function getStatusText(status) {
    const statusMap = {
        'submitted': 'На рассмотрении',
        'under_review': 'На рецензии',
        'review_completed': 'Рецензия готова',
        'revisions_required': 'Требуются правки',
        're-submitted_for_review': 'Отправлено для пересмотра',
        'accepted': 'Принята',
        'published': 'Опубликована',
        'rejected': 'Отклонена'
    };
    return statusMap[status] || status;
}

// Функция для форматирования даты
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('ru-RU', options);
}

// Функция для просмотра рукописи
function viewArticle(articleId) {
    window.location.href = `author_article_details.php?id=${articleId}`;
}

function loadManuscripts(searchParams = {}){
    const filteredParams = Object.fromEntries(
        Object.entries(searchParams).filter(([_, value]) => value !== '' && value !== undefined)
    );
    const queryString = new URLSearchParams(filteredParams).toString();

     fetch(`api/author/get_my_articles.php?${queryString}`)
        .then(response => response.json())
        .then(dataResponce => {
            const listDiv = document.getElementById('my-articles-list');
            
            if (dataResponce.status === 'success') {
                listDiv.innerHTML = '';
                
                if (dataResponce.data.length > 0) {
                    dataResponce.data.forEach(ms => {
                        const statusClass = `status-${ms.status}`;
                        const statusText = getStatusText(ms.status);
                        const submissionDate = formatDate(ms.submission_date);
                        const authors = parseAuthors(ms.authors);
                        
                        const manuscriptItem = document.createElement('div');
                        manuscriptItem.className = 'article-card';
                        manuscriptItem.innerHTML = `
                            <div class="article-header">
                                <h3 class="article-title">${ms.title}</h3>
                                <span class="article-status ${statusClass}">${statusText}</span>
                            </div>
                            <p class="article-meta">ID: ${ms.article_id}</p>
                            <p class="article-meta">Автор(ы): 
                            ${Array.isArray(authors) 
                                ? authors.map(author => 
                                    `${author.name} \(${author.email || 'Не указан'}\)`
                                ).join(', ')
                                : 'Нет данных об авторе'
                            }
                            </p>
                            <p class="article-meta">Дата подачи: ${submissionDate}</p>
                            <div class="article-actions">
                                <button class="view-btn" onclick="viewArticle(${ms.article_id})">
                                    Подробнее
                                </button>
                            </div>
                        `;
                        listDiv.appendChild(manuscriptItem);
                    });
                } else {
                    listDiv.innerHTML = '<div class="loading-message">Нет рукописей.</div>';
                }
            } else {
                listDiv.innerHTML = `<div class="error-message">Ошибка: ${dataResponce.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Ошибка AJAX:', error);
            document.getElementById('my-articles-list').innerHTML = `
                <div class="error-message">Не удалось загрузить данные рукописей. Пожалуйста, попробуйте позже.</div>
            `;
        });
}

function parseAuthors(authors){
    try {
        return typeof authors === 'string' ? JSON.parse(authors) : authors;
    } catch (e) {
        console.error('Ошибка разбора авторов:', e);
        return [];
    }
}

document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const searchParams = {
        title: document.getElementById('searchTitle').value.trim(),
        status: document.getElementById('searchStatus').value,
        author: document.getElementById('searchAuthor').value.trim()
    };
    
    loadManuscripts(searchParams);
});

document.getElementById('resetSearch').addEventListener('click', function() {
    document.getElementById('searchForm').reset();
    loadManuscripts();
});
        
document.addEventListener('DOMContentLoaded', function() {
   loadManuscripts();
});

