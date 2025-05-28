document.addEventListener('DOMContentLoaded', () => {
    // --- Элементы DOM ---
    const modalWindow = document.getElementById('modalWindow');
    const closeModalBtn = modalWindow.querySelector('.close-modal');
    const authContent = document.getElementById('authContent');
    const mainMessageDiv = document.getElementById('message'); // Главный блок сообщений модального окна

    // Формы и их контейнеры
    const loginFormContainer = document.getElementById('loginFormContainer');
    const loginForm = document.getElementById('loginForm');
    const registerFormContainer = document.getElementById('registerFormContainer');
    const registerForm = document.getElementById('registerForm');
    const forgotPasswordFormContainer = document.getElementById('forgotPasswordFormContainer');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');

    // Кнопки переключения форм
    const switchFormLinks = document.querySelectorAll('.switch-form');
    const forgotPasswordLink = document.getElementById('forgotPasswordLink');

    // Поля регистрации, связанные с рецензентом
    const isReviewerCheckbox = document.getElementById('isReviewer');
    const reviewerInterestsToggle = document.getElementById('reviewerInterestsToggle');
    const reviewerInterestsInput = document.getElementById('reviewerInterests');

    // --- Вспомогательные функции для UI ---

    function showModal(formToShowId) {
        modalWindow.style.display = 'block';
        showForm(formToShowId);
    }

    function hideModal() {
        modalWindow.style.display = 'none';
        clearMessages();
        clearAllFormErrors();
        resetForms(); // Сброс значений форм при закрытии
    }

    function showForm(formId) {
        // Скрываем все формы
        loginFormContainer.style.display = 'none';
        registerFormContainer.style.display = 'none';
        forgotPasswordFormContainer.style.display = 'none';

        // Показываем нужную форму
        document.getElementById(formId).style.display = 'block';
        clearMessages(); // Очищаем общие сообщения при переключении
        clearAllFormErrors(); // Очищаем ошибки при переключении
    }

    function showMessage(targetDiv, type, message) {
        targetDiv.textContent = message;
        targetDiv.className = `modal-message ${type}`;
        targetDiv.style.display = 'block';
    }

    function clearMessages(targetDiv = mainMessageDiv) {
        targetDiv.textContent = '';
        targetDiv.className = 'modal-message';
        targetDiv.style.display = 'none';
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

    function clearAllFormErrors() {
        document.querySelectorAll('.alertMessage').forEach(el => el.textContent = '');
        document.querySelectorAll('input.invalid, select.invalid').forEach(el => el.classList.remove('invalid'));
    }

    // Сброс значений форм
    function resetForms() {
        loginForm.reset();
        registerForm.reset();
        forgotPasswordForm.reset();
        isReviewerCheckbox.checked = false; // Отдельно для чекбокса
        toggleReviewerInterestsGroup(); // Сброс видимости поля интересов
    }

    // --- Обработчики событий модального окна и переключения форм ---

    closeModalBtn.addEventListener('click', hideModal);
    modalWindow.addEventListener('click', (e) => {
        if (e.target === modalWindow) { // Закрыть модальное окно при клике вне контента
            hideModal();
        }
    });

    // Открытие модального окна (пример, у вас будут свои кнопки)
    document.getElementById('openLoginModalBtn').addEventListener('click', () => showModal('loginFormContainer'));
    document.getElementById('openRegisterModalBtn').addEventListener('click', () => showModal('registerFormContainer'));

    // Переключение между формами
    switchFormLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetFormId = e.target.dataset.target;
            if (targetFormId) {
                showForm(targetFormId);
            }
        });
    });

    // Переход к форме "Забыли пароль?"
    forgotPasswordLink.addEventListener('click', (e) => {
        e.preventDefault();
        showForm('forgotPasswordFormContainer');
    });

    // --- Логика отображения поля "Интересы как рецензента" ---
    function toggleReviewerInterestsGroup() {
        if (isReviewerCheckbox.checked) {
            reviewerInterestsToggle.style.display = 'block';
            reviewerInterestsInput.required = true;
        } else {
            reviewerInterestsToggle.style.display = 'none';
            reviewerInterestsInput.required = false;
            reviewerInterestsInput.value = ''; // Очищаем значение при скрытии
            displayInputError(reviewerInterestsInput, document.getElementById('reviewerInterestsError'), ''); // Сброс ошибок
        }
    }
    isReviewerCheckbox.addEventListener('change', toggleReviewerInterestsGroup);
    // Инициализируем при загрузке, чтобы состояние было правильным, если форма открывается с уже отмеченным чекбоксом
    toggleReviewerInterestsGroup();

    // --- Функции валидации ---

    function isValidText(text) {
        return /^[a-zA-Zа-яА-ЯёЁ\s-']+$/.test(text.trim());
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidPassword(password) {
        // Пароль минимум 6 символов, содержит буквы и цифры
        return password.length >= 6 && /[a-zA-Z]/.test(password) && /\d/.test(password);
    }

    // Валидация формы входа
    function validateLoginForm() {
        let isValid = true;
        clearAllFormErrors(); // Очищаем все ошибки перед новой валидацией

        const email = loginForm.elements.email.value.trim();
        const password = loginForm.elements.password.value;

        if (!email) {
            displayInputError(loginForm.elements.email, document.getElementById('loginEmailError'), 'Email не может быть пустым.');
            isValid = false;
        } else if (!isValidEmail(email)) {
            displayInputError(loginForm.elements.email, document.getElementById('loginEmailError'), 'Введите корректный email.');
            isValid = false;
        }

        if (!password) {
            displayInputError(loginForm.elements.password, document.getElementById('loginPasswordError'), 'Пароль не может быть пустым.');
            isValid = false;
        }

        return isValid;
    }

    // Валидация формы регистрации
    function validateRegisterForm() {
        let isValid = true;
        clearAllFormErrors(); // Очищаем все ошибки перед новой валидацией

        const elements = registerForm.elements;
        const email = elements.email.value.trim();
        const password = elements.password.value;
        const confirmPassword = elements.regConfirmPassword.value;
        const name = elements.name.value.trim();
        const surname = elements.surname.value.trim();
        const organization = elements.organization.value.trim();
        const country = elements.country.value;
        const consentRule = elements.consentRule.checked;
        const isReviewer = elements.is_reviewer.checked;
        const reviewerInterests = elements.reviewer_interests.value.trim();

        if (!consentRule) {
            displayInputError(elements.consentRule, document.getElementById('consentRuleError'), 'Необходимо согласиться с Уведомлением о конфиденциальности.');
            isValid = false;
        }
        if (!email) {
            displayInputError(elements.email, document.getElementById('regEmailError'), 'Email не может быть пустым.');
            isValid = false;
        } else if (!isValidEmail(email)) {
            displayInputError(elements.email, document.getElementById('regEmailError'), 'Введите корректный email.');
            isValid = false;
        }

        if (!password) {
displayInputError(elements.password, document.getElementById('regPasswordError'), 'Пароль не может быть пустым.');
            isValid = false;
        } else if (password.length < 6) {
            displayInputError(elements.password, document.getElementById('regPasswordError'), 'Пароль должен быть не менее 6 символов.');
            isValid = false;
        }
        else if (!isValidPassword(password)) {
            displayInputError(elements.password, document.getElementById('regPasswordError'), 'Пароль должен содержать буквы и цифры.');
            isValid = false;
        }

        if (!confirmPassword) {
            displayInputError(elements.regConfirmPassword, document.getElementById('regConfirmPasswordError'), 'Подтвердите пароль.');
            isValid = false;
        } else if (password !== confirmPassword) {
            displayInputError(elements.regConfirmPassword, document.getElementById('regConfirmPasswordError'), 'Пароли не совпадают.');
            isValid = false;
        }

        if (!name) {
            displayInputError(elements.name, document.getElementById('regNameError'), 'Имя не может быть пустым.');
            isValid = false;
        } else if (!isValidText(name)) {
            displayInputError(elements.name, document.getElementById('regNameError'), 'Имя должно содержать только буквы, пробелы, дефисы и апострофы.');
            isValid = false;
        }

        if (!surname) {
            displayInputError(elements.surname, document.getElementById('regSurnameError'), 'Фамилия не может быть пустой.');
            isValid = false;
        } else if (!isValidText(surname)) {
            displayInputError(elements.surname, document.getElementById('regSurnameError'), 'Фамилия должна содержать только буквы, пробелы, дефисы и апострофы.');
            isValid = false;
        }

        if (!organization) {
            displayInputError(elements.organization, document.getElementById('regOrganizationError'), 'Организация не может быть пустой.');
            isValid = false;
        }

        if (!country) {
            displayInputError(elements.country, document.getElementById('regCountryError'), 'Выберите страну.');
            isValid = false;
        }

        if (!consentRule) {
            displayInputError(elements.consentRule, document.getElementById('consentRuleError'), 'Необходимо согласиться с Уведомлением о конфиденциальности.');
            isValid = false;
        }

        if (isReviewer && !reviewerInterests) {
            displayInputError(elements.reviewer_interests, document.getElementById('reviewerInterestsError'), 'Укажите интересы как рецензента.');
            isValid = false;
        }

        return isValid;
    }

    // Валидация формы "Забыли пароль?"
    function validateForgotPasswordForm() {
        let isValid = true;
        clearAllFormErrors();

        const email = forgotPasswordForm.elements.email.value.trim();

        if (!email) {
            displayInputError(forgotPasswordForm.elements.email, document.getElementById('forgotEmailError'), 'Email не может быть пустым.');
            isValid = false;
        } else if (!isValidEmail(email)) {
            displayInputError(forgotPasswordForm.elements.email, document.getElementById('forgotEmailError'), 'Введите корректный email.');
            isValid = false;
        }
        return isValid;
    }

    // --- Отправка форм через Fetch API ---

    // Обработчик для формы входа
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearMessages();
        if (!validateLoginForm()) {
            showMessage(mainMessageDiv, 'error', 'Пожалуйста, исправьте ошибки в форме.');
            return;
        }

        loginForm.querySelector('button[type="submit"]').disabled = true;
        showMessage(mainMessageDiv, 'info', 'Вход...');

        const formData = {
            email: loginForm.elements.email.value.trim(),
            password: loginForm.elements.password.value
        };

        try {
            const response = await fetch('/api/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (response.ok && data.status === 'success') {
                showMessage(mainMessageDiv, 'success', data.message || 'Вход выполнен успешно!');
                // После успешного входа, можно перенаправить пользователя
                // или обновить UI (например, скрыть модальное окно и показать личный кабинет)
                setTimeout(() => {
                    hideModal();
                    // window.location.href = '/dashboard.html'; // Пример редиректа
                    alert('Добро пожаловать, ' + data.user.first_name); // Временное сообщение
                    // Перезагрузка страницы для обновления навигации/UI
                    window.location.reload();
                }, 1500);
            } else {
                showMessage(mainMessageDiv, 'error', data.message || 'Ошибка входа. Проверьте email и пароль.');
            }
        } catch (error) {
            console.error('Ошибка входа:', error);
            showMessage(mainMessageDiv, 'error', 'Произошла ошибка сети или сервера. Пожалуйста, попробуйте позже.');
        } finally {
            loginForm.querySelector('button[type="submit"]').disabled = false;
        }
    });

    // Обработчик для формы регистрации
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearMessages();
        if (!validateRegisterForm()) {
            showMessage(mainMessageDiv, 'error', 'Пожалуйста, исправьте ошибки в форме.');
            return;
        }

        registerForm.querySelector('button[type="submit"]').disabled = true;
        showMessage(mainMessageDiv, 'info', 'Регистрация...');

        const formData = {
            email: registerForm.elements.email.value.trim(),
            password: registerForm.elements.password.value,
            name: registerForm.elements.name.value.trim(),
            surname: registerForm.elements.surname.value.trim(),
            organization: registerForm.elements.organization.value.trim(),
            country: registerForm.elements.country.value,
            consent_email: registerForm.elements.consent_email.checked,
            is_reviewer: registerForm.elements.is_reviewer.checked,
            consentRule: registerForm.elements.consentRule.checked,
            reviewer_interests: registerForm.elements.is_reviewer.checked ? registerForm.elements.reviewer_interests.value.trim() : null
        };

        try {
            const response = await fetch('/api/auth/register.php', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (response.ok && data.status === 'success') {
                showMessage(mainMessageDiv, 'success', data.message || 'Регистрация успешна! Пожалуйста, проверьте свою почту для подтверждения.');
                // Можно переключить на форму входа или просто закрыть модальное окно
                setTimeout(() => {
                    // hideModal();
                    showForm('loginFormContainer'); // Переключить на форму входа
                }, 3000);
            } else {
                // Серверная ошибка валидации или общая ошибка
                showMessage(mainMessageDiv, 'error', data.message || 'Ошибка регистрации.');
                if (data.errors) {
                    // Если сервер вернул детальные ошибки валидации
                    Object.keys(data.errors).forEach(field => {
                        const inputElement = registerForm.elements[field];
                        // Находим соответствующий элемент для ошибки по ID
                        const errorElementId = field + 'Error';
                        if (inputElement && document.getElementById(errorElementId)) {
                             displayInputError(inputElement, document.getElementById(errorElementId), data.errors[field]);
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Ошибка регистрации:', error);
            showMessage(mainMessageDiv, 'error', 'Произошла ошибка сети или сервера. Пожалуйста, попробуйте позже.');
        } finally {
            registerForm.querySelector('button[type="submit"]').disabled = false;
        }
    });

    // Обработчик для формы "Забыли пароль?"
    forgotPasswordForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const forgotPasswordMessagesDiv = document.getElementById('forgotPasswordMessages');
        clearMessages(forgotPasswordMessagesDiv); // Очищаем только сообщения этой формы
        if (!validateForgotPasswordForm()) {
            showMessage(forgotPasswordMessagesDiv, 'error', 'Пожалуйста, исправьте ошибки в форме.');
            return;
        }

        forgotPasswordForm.querySelector('button[type="submit"]').disabled = true;
        showMessage(forgotPasswordMessagesDiv, 'info', 'Отправка запроса...');

        const formData = {
            email: forgotPasswordForm.elements.email.value.trim()
        };

        try {
            const response = await fetch('/api/auth/forgot_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            // Всегда показываем общее сообщение для безопасности
            showMessage(forgotPasswordMessagesDiv, 'success', data.message || 'Если ваш email зарегистрирован, вам будет отправлена инструкция по сбросу пароля.');
            forgotPasswordForm.reset(); // Очищаем форму

        } catch (error) {
            console.error('Ошибка запроса сброса пароля:', error);
            showMessage(forgotPasswordMessagesDiv, 'error', 'Произошла ошибка сети или сервера. Пожалуйста, попробуйте позже.');
        } finally {
            forgotPasswordForm.querySelector('button[type="submit"]').disabled = false;
        }
    });
});