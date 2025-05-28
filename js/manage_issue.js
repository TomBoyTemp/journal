document.addEventListener('DOMContentLoaded', function() {
    const issueSelect = document.getElementById('issue-select');
    const publishBtn = document.getElementById('publish-issue');
    const acceptedArticlesList = document.getElementById('accepted-articles');
    const issueArticlesList = document.getElementById('issue-articles');
    const saveBtn = document.getElementById('save-changes');
    const responseDiv = document.getElementById('save-response');
    
    // Элементы модального окна публикации
    const publishModal = document.getElementById('publish-modal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelPublishBtn = document.getElementById('cancel-publish');
    const confirmPublishBtn = document.getElementById('confirm-publish');
    const publishDateInput = document.getElementById('publish-date');
    const publishResponseDiv = document.getElementById('publish-response');
    
    let allArticles = [];
    let currentIssueId = null;
    let issuesData = []; // Для хранения данных о выпусках
    
    // Установка текущей даты и времени по умолчанию
    const now = new Date();
    const timezoneOffset = now.getTimezoneOffset() * 60000;
    const localISOTime = (new Date(now - timezoneOffset)).toISOString().slice(0, 16);
    publishDateInput.value = localISOTime;
    
    // Загрузка выпусков
    fetch('/api/editor/get_issues.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                issuesData = data.data; // Сохраняем данные о выпусках
                issueSelect.innerHTML = '<option value="" hidden desabled>Выберите выпуск</option>' + 
                    data.data.map(issue => 
                        `<option value="${issue.id}">
                            Том ${issue.volume}, №${issue.issue_number} (${issue.year}) - ${issue.status}
                        </option>`
                    ).join('');
            } else {
                issueSelect.innerHTML = '<option value="">Ошибка загрузки выпусков</option>';
            }
        });
    
    // Загрузка принятых статей
    fetch('/api/editor/get_accepted_articles.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                allArticles = data.data;
                renderAcceptedArticles();
            } else {
                acceptedArticlesList.innerHTML = '<div class="error-message">Ошибка загрузки статей</div>';
            }
        });
    
    // Обработчик выбора выпуска
    issueSelect.addEventListener('change', function() {
        currentIssueId = this.value;
        if (currentIssueId) {
            loadIssueArticles(currentIssueId);
            saveBtn.disabled = false;
            
            // Показываем/скрываем кнопку публикации в зависимости от статуса выпуска
            const selectedIssue = issuesData.find(issue => issue.id == currentIssueId);
            if (selectedIssue && selectedIssue.status === 'draft') {
                publishBtn.style.display = 'inline-block';
            } else {
                publishBtn.style.display = 'none';
            }
        } else {
            issueArticlesList.innerHTML = '<div class="empty-message">Выберите выпуск</div>';
            saveBtn.disabled = true;
            publishBtn.style.display = 'none';
        }
    });
    
    // Обработчик сохранения
    saveBtn.addEventListener('click', function() {
        const articles = Array.from(issueArticlesList.children)
            .filter(el => el.dataset.articleId)
            .map((el, index) => ({
                id: el.dataset.articleId,
                order: index + 1
            }));
        
        const data = {
            issue_id: currentIssueId,
            articles: articles
        };
        
        fetch('/api/editor/manage_issue_articles.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                responseDiv.innerHTML = `<p style="color: green;">${data.message}</p>`;
            } else {
                responseDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            responseDiv.innerHTML = '<p style="color: red;">Произошла ошибка при сохранении</p>';
        });
    });
    
    // Обработчик кнопки публикации
    publishBtn.addEventListener('click', function() {
        publishModal.style.display = 'block';
    });
    
    // Закрытие модального окна
    closeModalBtn.addEventListener('click', function() {
        publishModal.style.display = 'none';
    });
    
    cancelPublishBtn.addEventListener('click', function() {
        publishModal.style.display = 'none';
    });
    
    // Подтверждение публикации
    confirmPublishBtn.addEventListener('click', function() {
        const publishDate = publishDateInput.value;
        
        fetch('/api/editor/publish_issue.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                issue_id: currentIssueId,
                publish_date: publishDate
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                publishResponseDiv.innerHTML = `<p style="color: green;">${data.message}</p>`;
                // Обновляем статус выпуска в интерфейсе
                const selectedOption = issueSelect.querySelector(`option[value="${currentIssueId}"]`);
                if (selectedOption) {
                    selectedOption.textContent = selectedOption.textContent.replace('draft', 'published');
                }
                publishBtn.style.display = 'none';
                
                // Закрываем модальное окно через 2 секунды
                setTimeout(() => {
                    publishModal.style.display = 'none';
                    publishResponseDiv.innerHTML = '';
                }, 2000);
            } else {
                publishResponseDiv.innerHTML = `<p style="color: red;">${data.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            publishResponseDiv.innerHTML = '<p style="color: red;">Произошла ошибка при публикации</p>';
        });
    });
    
    // Функции для работы с перетаскиванием
    function setupDragAndDrop() {
        const lists = [acceptedArticlesList, issueArticlesList];
        
        lists.forEach(list => {
            list.addEventListener('dragover', function(e) {
                e.preventDefault();
                const draggingItem = document.querySelector('.dragging');
                if (draggingItem) {
                    const afterElement = getDragAfterElement(list, e.clientY);
                    if (afterElement) {
                        list.insertBefore(draggingItem, afterElement);
                    } else {
                        list.appendChild(draggingItem);
                    }
                }
            });
        });
        
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.article-card:not(.dragging)')];
            
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }
    }
    
    function renderAcceptedArticles() {
        acceptedArticlesList.innerHTML = '';
        
        if (allArticles.length === 0) {
            acceptedArticlesList.innerHTML = '<div class="empty-message">Нет принятых статей</div>';
            return;
        }
        
        allArticles.forEach(article => {
            if (!article.issue_id || article.issue_id != currentIssueId) {
                const articleEl = createArticleElement(article);
                articleEl.draggable = true;
                
                articleEl.addEventListener('dragstart', () => {
                    if(issueSelect.value !=''){
                        articleEl.classList.add('dragging');
                    }
                });
                
                articleEl.addEventListener('dragend', () => {
                    articleEl.classList.remove('dragging');
                });
                
                acceptedArticlesList.appendChild(articleEl);
            }
        });
    }
    
    function loadIssueArticles(issueId) {
        fetch(`/api/editor/get_accepted_articles.php`)
            .then(response => response.json())
            .then(data => {
                issueArticlesList.innerHTML = '';
                
                if (data.status === 'success' && data.data.length > 0) {
                    data.data.forEach(article => {
                        if (article.issue_id == currentIssueId) {
                            const articleEl = createArticleElement(article);
                            articleEl.draggable = true;
                            
                            articleEl.addEventListener('dragstart', () => {
                                articleEl.classList.add('dragging');
                            });
                            
                            articleEl.addEventListener('dragend', () => {
                                articleEl.classList.remove('dragging');
                            });
                            
                            issueArticlesList.appendChild(articleEl);
                        }
                    });
                }
                
                // Обновляем список доступных статей
                renderAcceptedArticles();
            });
    }
    
    function createArticleElement(article) {
        const articleEl = document.createElement('div');
        articleEl.className = 'article-card';
        articleEl.dataset.articleId = article.id;
        console.log(article.authors);
        const authors = article.authors ? JSON.parse(article.authors) : [];

        const authorNames = authors.length > 0 ? authors.map(a => a.name).join(', ') : 'Не указан';
        
        articleEl.innerHTML = `
            <h3><a href="editors_manuscript_details.php?id=${article.id}" class="article-link">${article.title}</a></h3>
            <p>ID: ${article.id} | Автор: ${authorNames}</p>
        `;
        
        return articleEl;
    }
    
    // Инициализация drag and drop
    setupDragAndDrop();
});