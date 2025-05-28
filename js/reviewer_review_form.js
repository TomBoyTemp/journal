
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const articleId = urlParams.get('article_id');
    
    if (!articleId) {
        showError('Не указан ID статьи');
        return;
    }

    // Загружаем данные статьи
    fetch(`/api/reviewer/get_article_for_review.php?article_id=${articleId}`)
        .then(response => response.json())
        .then(data => {
            const loadingMessage = document.getElementById('loading-message');
            const errorMessage = document.getElementById('error-message');
            const articleDetails = document.getElementById('article-details');
            
            if (data.status === 'success') {
                displayArticleDetails(data.data);
                
                console.log("tyt");
                // Проверяем, отправлена ли уже рецензия
                if (data.data.has_submitted_review) {
                    disableReviewForm();
                    showMessage('Вы уже отправили рецензию на эту статью.', 'success');
                }
                
                articleDetails.style.display = 'block';
                loadingMessage.style.display = 'none';
            } else {
                showError(data.message || 'Ошибка загрузки данных статьи');
                loadingMessage.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showError('Не удалось загрузить данные статьи');
            document.getElementById('loading-message').style.display = 'none';
        });

    // Обработчик отправки формы
    document.getElementById('review-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = document.getElementById('submit-btn');
        const responseMessage = document.getElementById('response-message');
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Отправка...';
        
        const formData = {
            article_id: articleId,
            editor_comments: document.getElementById('editor_comments').value,
            author_comments: document.getElementById('author_comments').value,
            recommendation: document.getElementById('recommendation').value
        };

        fetch('/api/reviewer/submit_review.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showMessage(data.message, 'success');
                disableReviewForm();
            } else {
                showMessage(data.message || 'Ошибка при отправке рецензии', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Отправить рецензию';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Произошла ошибка при отправке формы', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Отправить рецензию';
        });
    });

    // Функции вспомогательные
    function displayArticleDetails(article) {
        document.getElementById('article-title').textContent = article.title;
        document.getElementById('article-id').textContent = article.id;
        
        // Настройка ссылок для скачивания
        const mainFileLink = document.getElementById('download-manuscript');
        if(article.file_path) {
            mainFileLink.href = `/api/user/download_manuscript.php?article_id=${articleId}&type=main`;
        } else {
            mainFileLink.style.display = 'none';
        }
        
        // Дополнительные файлы
        const suppFileLink = document.getElementById('manuscript-file-link');
        const suppFiles = article.supplementary_files ? JSON.parse(article.supplementary_files) : [];
        if(suppFiles.length > 0) {
            suppFileLink.href = `/api/user/download_manuscript.php?article_id=${articleId}&type=supplementary`;
        } else {
            suppFileLink.style.display = 'none';
        }
    }

    function disableReviewForm() {
        const form = document.getElementById('review-form');
        form.querySelectorAll('textarea, select, button').forEach(el => {
            el.disabled = true;
        });
        document.getElementById('submit-btn').textContent = 'Рецензия отправлена';
    }

    function showMessage(text, type) {
        const responseMessage = document.getElementById('response-message');
        responseMessage.style.display = 'block';
        responseMessage.className = `message ${type}-message`;
        responseMessage.innerHTML = `<p>${text}</p>`;
    }

    function showError(text) {
        const errorMessage = document.getElementById('error-message');
        errorMessage.textContent = text;
        errorMessage.style.display = 'block';
    }
});