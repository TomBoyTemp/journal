document.addEventListener('DOMContentLoaded', function() {

    const mainMessageDiv = document.getElementById('message'); // Главный блок сообщений модального окна

    const formFeed = document.getElementById('submissionForm-feed');
    // Инициализация Tagify для ключевых слов
    const input = document.querySelector('input[name="keywords"]');
    
    const tagify = new Tagify(input, {
        placeholder: "Введите ключевое слово и нажмите Enter",
        duplicates: false,
        maxTags: 10,
        whitelist: [], // Можно добавить предопределённые варианты
        dropdown: {
            enabled: 1, // Показывать предложения после 1 символа
            maxItems: 5
        }
    });

    // Добавление нового автора
    let authorCount = 1;
    document.getElementById('add-author').addEventListener('click', function() {
        authorCount++;
        const newAuthorBlock = document.createElement('div');
        newAuthorBlock.className = 'author-block-feed';
        newAuthorBlock.id = 'author-' + authorCount;
        
        newAuthorBlock.innerHTML = `
            <label for="author-name-${authorCount}" class="required">ФИО автора</label>
            <input type="text" id="author-name-${authorCount}" class="text-input-feed" name="authors[${authorCount-1}][name]" required>
            
            <label for="author-affiliation-${authorCount}" class="required">Аффилиация</label>
            <input type="text" id="author-affiliation-${authorCount}" class="text-input-feed" name="authors[${authorCount-1}][affiliation]" required>
            
            <label for="author-email-${authorCount}">Email</label>
            <input type="email" id="author-email-${authorCount}" class="text-input-feed" name="authors[${authorCount-1}][email]" >
            
            <button type="button" class="remove-author-feed" onclick="this.parentNode.remove()">Удалить автора</button>
        `;
        
        document.getElementById('authors-section-feed').insertBefore(newAuthorBlock, this);
    });

    function showMessage(targetDiv, type, message) {
        targetDiv.textContent = message;
        targetDiv.className = `${type}`;
        targetDiv.style.display = 'block';
    }

    formFeed.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(formFeed);

        fetch(formFeed.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
             if (data.status === 'success') {
                showMessage(mainMessageDiv, 'success', data.message || 'Статья отправлена!');
                // После успешной отправки, можно перенаправить пользователя
                // или обновить UI

                // window.location.href = '/dashboard.html'; // Пример редиректа
                alert('Статья подана'); // Временное сообщение
                // Перезагрузка страницы для обновления UI
                window.location.reload();
            } else {
                showMessage(mainMessageDiv, 'error', data.message || 'Проверьте правильность ввода');
            }
        })
        .catch(err => {
            console.log('Ошибка при отправке запроса: ', err);
        })
    });
});