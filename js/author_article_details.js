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

function formatLabel(key) {
    const labels = {
        'article_id': 'ID статьи',
        'title': 'Название',
        'abstract': 'Аннотация',
        'keywords': 'Ключевые слова',
        'version': 'Версия рукописи',
        'section_name': 'Рубрика',
        'submission_date': 'Дата подачи',
        'status': 'Статус',
        'submitted_by_name': 'Отправитель',
    };
    return labels[key] || key;
    }
        
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const articleId = urlParams.get('id');
    const listDiv = document.getElementById('article-details');

    fetch(`api/author/get_article_details.php?id=${articleId}`)
        .then(response => response.json())
        .then(data => {
            const listDiv = document.getElementById('article-details');
            
            if (data.status === 'success') {
                listDiv.innerHTML = '';
                
                if(data.data.length > 0){

                    document.getElementById('download-manuscript').href = `api/user/download_manuscript.php?article_id=${articleId}&type=main`;
                    document.getElementById('manuscript-file-link').href = `api/user/download_manuscript.php?article_id=${articleId}&type=supplementary`;

                    listDiv.appendChild(displayArticleDetails(data.data[0]));
                    if(data.data[0].status === 'revisions_required'){
                        displayUploadSection(data.data[0]);    
                    }
               
                } else {
                    listDiv.innerHTML = '<div class="loading-message">Нет данной рукописи.</div>';
                }
            } else {
                listDiv.innerHTML = `<div class="error-message">Ошибка: ${dataResponce.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Ошибка AJAX:', error);
            document.getElementById('article-details').innerHTML = `
                <div class="error-message">Не удалось загрузить данные рукописей. Пожалуйста, попробуйте позже.</div>
            `;
        });


    function displayArticleDetails(article){
        const container = document.createElement('div');

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

        if(article && Object.keys(article).length > 0){
            const excludedFields = [
                'first_author_name',
                'editor_decision_comments',
                'file_path',          // Исключаем путь к файлу
                'supplementary_files' // Исключаем дополнительные файлы
            ];
            const detailsHtml = Object.entries(article)
            .filter(([key]) => !excludedFields.includes(key)) // Исключаем авторов из общего списка
            .map(([key, value]) => {
            let displayValue = value;
            
            if (value === null) {
                displayValue = 'Не указано';
            } else if (key === 'submission_date') {
                displayValue = new Date(value).toLocaleString();
            } else if (key === 'status') {
                displayValue = getStatusText(value);
            }
            
            return `
            <div class="detail-row">
                <div class="detail-label">${formatLabel(key)}</div>
                <div class="detail-value">${displayValue}</div>
            </div>
            `;
        }).join('');
        
        // Добавляем авторов отдельно
            const authorsHtml = `
            <div class="detail-row">
            <div class="detail-label">Автор(ы)</div>
            <div class="detail-value">
                ${article.first_author_name.map(author => `
                    <div>${author.name} (${author.email})</div>
                `).join('')}
            </div>
            </div>
            `;
            
            container.innerHTML = detailsHtml + authorsHtml;
        } else {
            container.innerHTML = '<div class="loading-message">Нет данных о рукописи.</div>';
        }
    
        return container;
    }

    function displayUploadSection(article){
        const container = document.createElement('div');
        container.className = 'article-container';
        container.innerHTML = `
            <h2 class="section-title">Загрузить пересмотренную версию</h2>
            <p><strong>Комментарии редактора:</strong>${article.editor_decision_comments}</p>
            <form id="revision-upload-form" enctype="multipart/form-data">

                <input type="hidden" name="article_id" value="${article.article_id}">
                <div class="form-group">
                    <label for="revision_file" class="form-label">Выберите файл пересмотренной рукописи:</label>
                    <input type="file" id="revision_file" name="revision_file" required>
                </div>
                <div class="form-group">
                    <label for="revision_comments" class="form-label">Комментарии к этой версии (что изменено):</label>
                    <textarea id="revision_comments" name="comments" class="form-textarea"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Загрузить</button>
            </form>
            <div id="revision-response"></div>
        `;
        listDiv.after(container);

        document.getElementById('revision-upload-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch('/api/author/upload_revision.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                const msgDiv = document.getElementById('revision-response');
                if (result.status === 'success') {
                    msgDiv.innerHTML = `<p style="color: green;">${result.message}</p>`;
                    location.reload(); // Перезагрузить страницу
                } else {
                    msgDiv.innerHTML = `<p style="color: red;">Ошибка: ${result.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки ревизии:', error);
                document.getElementById('revision-response').innerHTML = '<p style="color: red;">Произошла ошибка при загрузке пересмотренной версии.</p>';
            });
        });
    }
});

