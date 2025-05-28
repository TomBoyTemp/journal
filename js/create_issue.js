document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('create-issue-form');
    const responseDiv = document.getElementById('form-response');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            volume: parseInt(document.getElementById('volume').value),
            issue_number: parseInt(document.getElementById('issue_number').value),
            year: parseInt(document.getElementById('year').value),
            publication_date: document.getElementById('publication_date').value || null
        };
        
        fetch('/api/editor/create_issue.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                responseDiv.innerHTML = `<p style="color: green;">${data.message}</p>`;
                form.reset();
                // setTimeout(() => {
                //     window.location.href = 'issues.php'; // Перенаправление на страницу выпусков
                // }, 1500);
            } else {
                let errorMessage = data.message;
                if (Array.isArray(data.message)) {
                    errorMessage = data.message.join('<br>');
                }
                responseDiv.innerHTML = `<p style="color: red;">${errorMessage}</p>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            responseDiv.innerHTML = '<p style="color: red;">Произошла ошибка при отправке формы</p>';
        });
    });
});