
document.addEventListener('DOMContentLoaded', () => {
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    const newPasswordInput = document.getElementById('new_password');
    const confirmNewPasswordInput = document.getElementById('confirm_new_password');
    const resetPasswordButton = document.getElementById('resetPasswordButton');
    const messagesDiv = document.getElementById('messages');
    const newPasswordError = document.getElementById('newPasswordError');
    const confirmNewPasswordError = document.getElementById('confirmNewPasswordError');

    // Получаем токен из URL
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');

    // Если токена нет, не позволяем сбросить пароль
    if (!token) {
        showMessage('error', 'Отсутствует токен для сброса пароля. Пожалуйста, используйте ссылку из письма.');
        resetPasswordForm.style.display = 'none'; // Скрываем форму
        return;
    }

    function showMessage(type, message) {
        messagesDiv.textContent = message;
        messagesDiv.className = `message ${type}`;
        messagesDiv.style.display = 'block';
    }

    function clearMessages() {
        messagesDiv.textContent = '';
        messagesDiv.className = 'message';
        messagesDiv.style.display = 'none';
    }

    function displayInputError(inputElement, errorElement, message) {
        if (message) {
            errorElement.textContent = message;
            inputElement.classList.add('invalid');
        } else {
            errorElement.textContent = '';
            inputElement.classList.remove('invalid');
        }
    }

    function clearAllErrors() {
        displayInputError(newPasswordInput, newPasswordError, '');
        displayInputError(confirmNewPasswordInput, confirmNewPasswordError, '');
    }

    resetPasswordForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearMessages();
        clearAllErrors();
        resetPasswordButton.disabled = true;

        const newPassword = newPasswordInput.value;
        const confirmNewPassword = confirmNewPasswordInput.value;

        // Клиентская валидация
        let isValid = true;
        if (!newPassword) {
            displayInputError(newPasswordInput, newPasswordError, 'Введите новый пароль.');
            isValid = false;
        } else if (newPassword.length < 6) {
            displayInputError(newPasswordInput, newPasswordError, 'Пароль должен быть не менее 6 символов.');
            isValid = false;
        }

        if (!confirmNewPassword) {
            displayInputError(confirmNewPasswordInput, confirmNewPasswordError, 'Подтвердите новый пароль.');
            isValid = false;
        } else if (newPassword !== confirmNewPassword) {
            displayInputError(confirmNewPasswordInput, confirmNewPasswordError, 'Пароли не совпадают.');
            isValid = false;
        }

        if (!isValid) {
            showMessage('error', 'Пожалуйста, исправьте ошибки в форме.');
            resetPasswordButton.disabled = false;
            return;
        }

        showMessage('info', 'Сброс пароля...');

        try {
            const response = await fetch('/api/auth/reset_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    token: token,
                    new_password: newPassword
                })
            });

            const data = await response.json();

            if (response.ok && data.status === 'success') {
                showMessage('success', data.message + ' Вы будете перенаправлены на страницу входа.');
                setTimeout(() => {
                    window.location.href = '/index.php'; // Перенаправить на страницу входа
                }, 3000);
            } else {
                showMessage('error', data.message || 'Ошибка сброса пароля.');
                // Если сервер вернул конкретные ошибки, можно их отобразить
                if (data.message.includes('Срок действия токена истек') || data.message.includes('Недействительный или уже использованный токен')) {
                    resetPasswordForm.style.display = 'none'; // Скрыть форму, если токен недействителен
                }
            }
        } catch (error) {
            console.error('Ошибка при сбросе пароля:', error);
            showMessage('error', 'Произошла ошибка сети или сервера. Пожалуйста, попробуйте позже.');
        } finally {
            resetPasswordButton.disabled = false;
        }
    });
});