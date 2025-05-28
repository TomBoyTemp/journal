document.addEventListener('DOMContentLoaded', function() {
    // Получаем ID статьи из URL
    const urlParams = new URLSearchParams(window.location.search);
    const articleId = urlParams.get('id');
    
    if (!articleId) {
        showError('Не указан ID статьи');
        return;
    }
    
    // Загружаем данные о рукописи
    fetch(`/api/editor/get_manuscript_full.php?id=${articleId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayManuscriptDetails(data.data);
                document.getElementById('manuscript-details').style.display = 'block';
                displayReviews(data.data.reviews);
                document.getElementById('reviews-section').style.display = 'block';
                
                if (data.data.manuscript.status !== 'rejected' && data.data.manuscript.status !== 'published')
                {
                    populateReviewersSelect(data.data.potential_reviewers);
                    displayAssignedReviewers(data.data);
                    document.getElementById('reviewers-section').style.display = 'block';
                    document.getElementById('decision-section').style.display = 'block';
                }
                document.getElementById('loading-message').style.display = 'none';
                
                // Настройка ссылки на скачивание
                document.getElementById('download-manuscript').href = `api/user/download_manuscript.php?article_id=${articleId}&type=main`;
                document.getElementById('manuscript-file-link').href = `api/user/download_manuscript.php?article_id=${articleId}&type=supplementary`;
            } else {
                showError(data.message || 'Ошибка загрузки данных');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showError('Не удалось загрузить данные о рукописи');
        });
        
    
    // Обработчик назначения рецензентов
    document.getElementById('assign-reviewers-btn').addEventListener('click', function() {
        const select = document.getElementById('reviewers-select');
        const selectedReviewers = Array.from(select.selectedOptions).map(option => option.value);
        const deadline = document.getElementById('review-deadline').value;
        
        if (selectedReviewers.length === 0 || !deadline) {
            alert('Пожалуйста, выберите рецензентов и укажите срок');
            return;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0); 

        const selectedDate = new Date(deadline);
        selectedDate.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            alert('Дата рецензирования не может быть раньше текущей даты');
            return;
        }
        
        const payload = {
            article_id: articleId,
            reviewer_ids: selectedReviewers,
            deadline: deadline
        };
        
        fetch('/api/editor/assign_reviewers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Рецензенты успешно назначены');
                location.reload(); // Обновляем страницу для отображения изменений
            } else {
                showError(data.message || 'Ошибка при назначении рецензентов');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showError('Не удалось назначить рецензентов');
        });
    });

    // Обработчик формы принятия решения
    document.getElementById('editor-decision-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const decisionData = {
            article_id: articleId,
            decision: formData.get('decision'),
            comments: formData.get('comments')
        };

        fetch('/api/editor/submit_decision.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(decisionData)
        })
        .then(response => response.json())
        .then(data => {
            const responseDiv = document.getElementById('decision-response');
            if (data.status === 'success') {
                responseDiv.className = 'success-message';
                responseDiv.innerHTML = `<p>${data.message}</p>`;
                // location.reload(); 
            } else {
                responseDiv.className = 'error-message';
                responseDiv.innerHTML = `<p>Ошибка: ${data.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            const responseDiv = document.getElementById('decision-response');
            responseDiv.className = 'error-message';
            responseDiv.innerHTML = '<p>Произошла ошибка при отправке решения</p>';
        });
    });
    
    // Функция для отображения ошибок
    function showError(message) {
        const errorDiv = document.getElementById('error-message');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        document.getElementById('loading-message').style.display = 'none';
    }
    
    // Функция для отображения деталей рукописи
    function displayManuscriptDetails(data) {
        const manuscript = data.manuscript;
        document.getElementById('manuscript-title').textContent = manuscript.title;
        document.getElementById('manuscript-id').textContent = manuscript.id;

        const authors = manuscript.authors?.first_author_name || manuscript.submitted_by_name;
        document.getElementById('manuscript-authors').textContent = manuscript.authors.first_author_name;
        document.getElementById('manuscript-date').textContent = formatDate(manuscript.submission_date);
        document.getElementById('manuscript-status').textContent = getStatusText(manuscript.status);
        document.getElementById('manuscript-keywords').textContent = manuscript.keywords || 'Не указаны';
        document.getElementById('manuscript-abstract').textContent = manuscript.abstract;
    }
    
    // Функция для отображения назначенных рецензентов
    function displayAssignedReviewers(reviewersData) {
        const container = document.getElementById('assigned-reviewers');
        container.innerHTML = '';

        if (!reviewersData || !reviewersData.assigned_reviewers || !Array.isArray(reviewersData.assigned_reviewers)) {
            container.innerHTML = '<p class="text-muted">Ошибка загрузки списка рецензентов</p>';
            return;
        }

        if (reviewersData.assigned_reviewers.length === 0) {
            container.innerHTML = '<p class="text-muted">Рецензенты еще не назначены</p>';
            return;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        reviewersData.assigned_reviewers.forEach(reviewer => {
            if (!reviewer) return;

            const card = document.createElement('div');
            card.className = 'reviewer-card';
            
            const reviewerName = reviewer.reviewer_username || 'Неизвестный рецензент';
            const reviewerEmail = reviewer.reviewer_email || 'Email не указан';
            const status = reviewer.invitation_status || 'Статус неизвестен';
            const inviteDate = reviewer.invitation_date ? formatDate(reviewer.invitation_date) : 'Дата не указана';

            const deadline = reviewer.review_deadline ? new Date(reviewer.review_deadline) : null;
            const isOverdue = deadline && deadline < today;
            const deadlineText = reviewer.review_deadline ? formatDate(reviewer.review_deadline) : 'Не указан';
            const deadlineClass = isOverdue ? 'text-danger' : '';
            
            // Добавляем стиль для всей карточки, если срок просрочен
            if (isOverdue) {
                card.style.borderLeft = '4px solid #dc3545';
            }

            card.innerHTML = `
                <div class="reviewer-header">
                    <div>
                        <span class="reviewer-name">${reviewerName}</span>
                        <div class="text-muted small">${reviewerEmail}</div>
                    </div>
                    <button class="btn btn-danger" onclick="unassignReviewer(${reviewer.reviewer_id}, ${reviewersData.manuscript.id})">
                        Удалить
                    </button>
                </div>
                <div class="reviewer-meta">
                    <div>Статус: ${getReviewStatusText(status)}</div>
                    <div>Приглашен: ${inviteDate}</div>
                    <div class="${deadlineClass}">Срок: ${deadlineText} ${isOverdue ? '(Просрочено)' : ''}</div>
                </div>
            `;
            container.appendChild(card);
        });
    }

    // Функция для отображения рецензий
    function displayReviews(reviews) {
        const container = document.getElementById('reviews-list');
        container.innerHTML = '';

        if (!reviews || !Array.isArray(reviews) || reviews.length === 0) {
            container.innerHTML = '<p>Пока нет полученных рецензий.</p>';
            return;
        }

        reviews.forEach(review => {
            const card = document.createElement('div');
            card.className = 'review-card';
            
            const reviewerName = review.reviewer_username || 'Неизвестный рецензент';
            const reviewDate = review.review_date ? formatDate(review.review_date) : 'Дата не указана';

            card.innerHTML = `
                <div class="review-header">
                    <div>
                        <span class="reviewer-name">${reviewerName}</span>
                        <div class="text-muted small">${reviewDate}</div>
                    </div>
                </div>
                <div class="review-meta">
                    <p><strong>Рекомендация:</strong> ${getRecommendationText(review.recommendation)}</p>
                    <p><strong>Комментарии для редактора:</strong></p>
                    <div style="white-space: pre-wrap;">${review.comments_for_editor}</div>
                    <p><strong>Комментарии для автора:</strong></p>
                    <div style="white-space: pre-wrap;">${review.comments_for_author || 'Нет комментариев'}</div>
                </div>
            `;
            container.appendChild(card);
        });
    }
    
    // Функция для заполнения списка доступных рецензентов
    function populateReviewersSelect(reviewersData) {
        const select = document.getElementById('reviewers-select');
        select.innerHTML = '<option value="" disabled>Выберите рецензента...</option>';
        
        if (!reviewersData?.length) {
            console.error('Нет данных о рецензентах');
            select.disabled = true;
            return;
        }

        reviewersData.forEach(reviewer => {
            if (!reviewer?.id) return;
            
            const option = document.createElement('option');
            option.value = reviewer.id;
            
            const name = reviewer.reviewer_username || reviewer.email.split('@')[0] || `Рецензент #${reviewer.id}`;
            const shortInterests = reviewer.reviewer_interests 
                ? reviewer.reviewer_interests.split(',').slice(0, 2).join(', ') + 
                (reviewer.reviewer_interests.split(',').length > 2 ? '...' : '')
                : 'без указания интересов';
            
            option.textContent = `${name} (${shortInterests})`;
            option.title = `${name}\nИнтересы: ${reviewer.reviewer_interests || 'не указаны'}`;
            
            select.appendChild(option);
        });
    }
    
    // Функция для форматирования даты
    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('ru-RU', options);
    }
    
    // Функция для преобразования статуса
    function getStatusText(status) {
        const statusMap = {
            'submitted': 'На рассмотрении',
            'under_review': 'На рецензии',
            'review_completed': 'Рецензия готова',
            'revision_requested': 'Требуются правки',
            'accepted': 'Принята',
            'published': 'Опубликована',
            'rejected': 'Отклонена'
        };
        return statusMap[status] || status;
    }
    
    // Функция для преобразования статуса рецензии
    function getReviewStatusText(status) {
        const statusMap = {
            'Pending': 'Ожидает ответа',
            'Declined': 'Отклонено',
            'Accepted': 'Принято',
            'Completed': 'Завершена',
            'overdue': 'Просрочена'
        };
        return statusMap[status] || status;
    }

    // Функция для преобразования рекомендации рецензента
    function getRecommendationText(recommendation) {
        const recommendationMap = {
            'accept': 'Принять',
            'minor_revisions': 'Незначительные доработки',
            'major_revisions': 'Серьезные доработки',
            'reject': 'Отклонить'
        };
        return recommendationMap[recommendation] || recommendation;
    }
});

// Функция для удаления рецензента (должна быть глобальной для вызова из HTML)
function unassignReviewer(reviewerId, articleId) {
    if (!confirm('Вы уверены, что хотите удалить этого рецензента?')) {
        return;
    }
    
    fetch('/api/editor/unassign_reviewer.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            reviewer_id: reviewerId,
            article_id: articleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Рецензент успешно удален');
            location.reload();
        } else {
            alert('Ошибка: ' + (data.message || 'Не удалось удалить рецензента'));
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Не удалось выполнить запрос');
    });
}