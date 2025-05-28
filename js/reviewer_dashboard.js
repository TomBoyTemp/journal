document.addEventListener('DOMContentLoaded', function() {
    loadReviewInvitations();
});

function loadReviewInvitations() {
    fetch('/api/reviewer/get_review_invitations.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('review-invitations');
            const loadingMessage = document.getElementById('loading-message');
            const errorMessage = document.getElementById('error-message');
            
            if (data.status === 'success') {
                container.innerHTML = '';
                
                if (data.data.length > 0) {
                    data.data.forEach(invitation => {
                        const card = createInvitationCard(invitation);
                        container.appendChild(card);
                    });
                } else {
                    container.innerHTML = '<div class="empty-message">У вас нет приглашений на рецензирование</div>';
                }
                
                container.style.display = 'block';
                loadingMessage.style.display = 'none';
            } else {
                errorMessage.textContent = data.message || 'Ошибка загрузки приглашений';
                errorMessage.style.display = 'block';
                loadingMessage.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            document.getElementById('error-message').textContent = 'Не удалось загрузить приглашения';
            document.getElementById('error-message').style.display = 'block';
            document.getElementById('loading-message').style.display = 'none';
        });
}

function createInvitationCard(invitation) {
    const card = document.createElement('div');
    card.className = 'invitation-card';
    const deadlineText = invitation.review_deadline ? 
                    `До ${formatDate(invitation.review_deadline)}` : 
                    'Срок не указан';
    
    // Определяем статус и доступные действия
    let statusText = '';
    let statusClass = '';
    let actions = '';
    let showDownloadButton = true;
    
    switch(invitation.invitation_status) {
        case 'Pending':
            statusText = 'Ожидает вашего ответа';
            statusClass = 'status-pending';
            actions = `
                <button class="btn btn-primary" onclick="respondToInvitation(${invitation.article_id}, 'accept')">
                    Принять приглашение
                </button>
                <button class="btn btn-danger" onclick="respondToInvitation(${invitation.article_id}, 'decline')">
                    Отклонить приглашение
                </button>
            `;
            break;
            
        case 'Accepted':
            statusText = 'Принято';
            statusClass = 'status-accepted';
            
            if (invitation.review_submitted) {
                actions = `
                    <button class="btn btn-disabled" disabled>
                        Рецензия отправлена
                    </button>
                    <a href="reviewer_review_view.php?article_id=${invitation.article_id}" class="btn btn-secondary">
                        Просмотреть рецензию
                    </a>
                `;
            } else {
                actions = `
                    <a href="reviewer_review_form.php?article_id=${invitation.article_id}" class="btn btn-primary">
                        Написать рецензию
                    </a>
                `;
            }
            break;
            
        case 'Declined':
            statusText = 'Отклонено';
            statusClass = 'status-declined';
            actions = `
                <button class="btn btn-disabled" disabled>
                    Приглашение отклонено
                </button>
            `;
            showDownloadButton = false;
            break;
            
        case 'Completed':
            statusText = 'Завершено';
            statusClass = 'status-completed';
            actions = `
                <a href="reviewer_review_view.php?article_id=${invitation.article_id}" class="btn btn-secondary">
                    Просмотреть рецензию
                </a>
            `;
            break;
    }
    
    // Формируем карточку
    card.innerHTML = `
        <div class="invitation-header">
            <h3 class="invitation-title">${invitation.article_title}</h3>
            <span class="invitation-status ${statusClass}">${statusText}</span>
        </div>
        
        <div class="invitation-meta">
            <div class="meta-item">
                <span class="meta-label">ID статьи:</span>
                <span>${invitation.article_id}</span>
            </div>
            
            <div class="meta-item">
                <span class="meta-label">Дата приглашения:</span>
                <span>${formatDate(invitation.invitation_date)}</span>
            </div>
                <div class="meta-item">
                <span class="meta-label">Срок:</span>
                <span>${deadlineText}</span>
            </div>

            ${invitation.article_authors ? `
            <div class="meta-item">
                <span class="meta-label">Авторы:</span>
                <span>${invitation.article_authors}</span>
            </div>
            ` : ''}
            
            ${invitation.article_keywords ? `
            <div class="meta-item">
                <span class="meta-label">Ключевые слова:</span>
                <span>${invitation.article_keywords}</span>
            </div>
            ` : ''}
        </div>
        
        ${invitation.article_abstract ? `
        <div class="meta-item" style="width:100%">
            <span class="meta-label">Аннотация:</span>
            <p>${invitation.article_abstract}</p>
        </div>
        ` : ''}
        
        <div class="invitation-actions">
            ${actions}
            ${showDownloadButton ? `
            <a href="api/user/download_manuscript.php?article_id=${invitation.article_id}&type=main" class="btn btn-secondary">
                Скачать рукопись статьи
            </a>
            ` : ''}
        </div>
    `;
    
    return card;
}

function respondToInvitation(articleId, action) {
    if (!confirm(`Вы уверены, что хотите ${action === 'accept' ? 'принять' : 'отклонить'} это приглашение?`)) {
        return;
    }
    
    fetch('/api/reviewer/respond_to_invitation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            article_id: articleId,
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            loadReviewInvitations(); // Обновляем список
        } else {
            alert('Ошибка: ' + (data.message || 'Не удалось обработать запрос'));
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Не удалось выполнить запрос');
    });
}

function formatDate(dateString) {
    if (!dateString) return 'Не указана';
    
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    
    return new Date(dateString).toLocaleDateString('ru-RU', options);
}