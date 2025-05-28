    
    // Функция для преобразования статуса в читаемый формат
    function getStatusText(status) {
        const statusMap = {
            'submitted': 'На рассмотрении',
            'under_review': 'На рецензии',
            'review_completed': 'Рецензия готова',
            'revision_requested': 'Требуются правки',
            'accepted': 'Принята',
            'published': 'Опубликована',
            'rejected': 'Отклонена',
            're-submitted_for_review': 'Отправлено на правки'
        };
        return statusMap[status] || status;
    }

    // Функция для форматирования даты
    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('ru-RU', options);
    }

    // Функция для просмотра рукописи
    function viewManuscript(articleId) {
        window.location.href = `editors_manuscript_details.php?id=${articleId}`;
    }
        
    function loadManuscripts(searchParams = {}) {
        const filteredParams = Object.fromEntries(
            Object.entries(searchParams).filter(([_, value]) => value !== '' && value !== undefined)
        );
        const queryString = new URLSearchParams(filteredParams).toString();

        fetch(`api/editor/get_manuscripts.php?${queryString}`)
        .then(response => response.json())
        .then(dataResponce => {
            const listDiv = document.getElementById('manuscripts-list');
            
            if (dataResponce.status === 'success') {
                listDiv.innerHTML = '';
                
                if (dataResponce.data.length > 0) {
                    dataResponce.data.forEach(ms => {
                        const statusClass = `status-${ms.status}`;
                        const statusText = getStatusText(ms.status);
                        const submissionDate = formatDate(ms.submission_date);
                        const authors = parseAuthors(ms.authors);
                        
                        const manuscriptItem = document.createElement('div');
                        manuscriptItem.className = 'manuscript-card';
                        manuscriptItem.innerHTML = `
                            <div class="manuscript-header">
                                <h3 class="manuscript-title">${ms.title}</h3>
                                <span class="manuscript-status ${statusClass}">${statusText}</span>
                            </div>
                            <p class="manuscript-meta">ID: ${ms.article_id}</p>
                                <p class="manuscript-meta">Автор(ы): 
                            ${Array.isArray(authors) 
                                ? authors.map(author => 
                                    `${author.name} \(${author.email || 'Не указан'}\)`
                                ).join(', ')
                                : 'Нет данных об авторе'
                            }
                            <p class="manuscript-meta">Дата подачи: ${submissionDate}</p>
                            <div class="manuscript-actions">
                                <button class="view-btn" onclick="viewManuscript(${ms.article_id})">
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
            document.getElementById('manuscripts-list').innerHTML = `
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

    //Обработчик формы поиска
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const searchParams = {
            title: document.getElementById('searchTitle').value.trim(),
            status: document.getElementById('searchStatus').value,
            author: document.getElementById('searchAuthor').value.trim()
        };
        
        loadManuscripts(searchParams);
    });

    //Сброс поиска
    document.getElementById('resetSearch').addEventListener('click', function() {
        document.getElementById('searchForm').reset();
        loadManuscripts();
    });

    document.addEventListener('DOMContentLoaded', function() {
        loadManuscripts();
    });