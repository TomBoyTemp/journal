document.addEventListener('DOMContentLoaded', function() {
    // Получаем ID статьи из URL
    const urlParams = new URLSearchParams(window.location.search);
    const articleId = urlParams.get('id');
    
    if (!articleId) {
        showError('Не указан ID статьи');
        return;
    }
    
    // Загружаем данные о рукописи
    fetch(`/api/user/get_published_article.php?id=${articleId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayManuscriptDetails(data.data);
                
                document.getElementById('manuscript-details').style.display = 'block';
                document.getElementById('loading-message').style.display = 'none';

                
                // Настройка ссылки на скачивание
                document.getElementById('download-manuscript').href = `api/user/download_manuscript.php?article_id=${articleId}&type=supplementary`;
                document.getElementById('manuscript-file-link').href = `api/user/download_manuscript.php?article_id=${articleId}&type=main`;
            } else {
                showError(data.message || 'Ошибка загрузки данных');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showError('Не удалось загрузить данные о рукописи');
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
        const manuscript = data;
        document.getElementById('manuscript-title').textContent = manuscript.title;
        document.getElementById('manuscript-id').textContent = manuscript.id;

        const authors = manuscript.authors?.first_author_name || manuscript.submitted_by_name;
        document.getElementById('manuscript-authors').textContent = manuscript.authors_list;
        document.getElementById('manuscript-date').textContent = formatDate(manuscript.publication_date);
        document.getElementById('manuscript-keywords').textContent = manuscript.keywords || 'Не указаны';
        document.getElementById('manuscript-abstract').textContent = manuscript.abstract;
    }

    
    // Функция для форматирования даты
    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('ru-RU', options);
    }
});
