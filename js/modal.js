document.addEventListener('DOMContentLoaded', function() {

    // Список поддерживаемых провайдеров
    const oauthProviders = {
        vk: {
            authUrl: 'https://id.vk.com/authorize',
            clientId: '53582115',
            redirectUri: 'http://localhost/',
            scope: 'vkid.personal_info',
            codeChallengeMethod: 'S256'
        },
        github: {
            authUrl: 'https://github.com/login/oauth/authorize',
            clientId: 'YOUR_GITHUB_CLIENT_ID',
            redirectUri: '/github_callback.php',
            scope: 'user:email',
            codeChallengeMethod: 'S256'
        },
        yandex: {
            authUrl: 'https://oauth.yandex.ru/authorize',
            clientId: '45e607a454064085a6ffd50ff58a88c6',
            redirectUri: 'http://localhost/',
            scope: 'login:email login:info',
            codeChallengeMethod: 'S256'
        }
    };

    //Для формы восстановления пароля
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const emailInput = document.getElementById('email');
    const submitEmailButton = document.getElementById('submitEmailButton');
    const messagesDiv = document.getElementById('messages');
    const emailError = document.getElementById('emailError');


    const formLogin = document.getElementById('login');
    const formRegister = document.getElementById('register');

    const warningEmail = document.getElementById('warningEmail');
    const warningPassw = document.getElementById('warningPassw');
    
    const passwd = document.getElementById('regPassword');
    const passwdConfirm = document.getElementById('regConfirm');

    const regEmail = document.getElementById('regEmail');

    // Элементы интерфейса
    const authModal = document.getElementById('modalWindow');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    const loginBtn = document.getElementById('login-button');
    const registerBtn = document.getElementById('register-button');

    // const loginBtn = document.getElementsByClassName('loginBtn');
    // const registerBtn = document.getElementsByClassName('registerBtn');
    const closeModal = document.querySelector('.close-modal');
    const switchForms = document.querySelectorAll('.switch-form');

    const forms = document.querySelectorAll('.auth-form > form');
    const message = document.getElementById('message');
    
    //Отправка формы на сервер
    forms.forEach( form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if(!identityPassword()){
                passwdConfirm.focus();
                return;
            }

            // if(!regEmail.value && !validateEmail()){
            //     warningEmail.textContent = "Пожалуйста, введите корректный email-адрес (например, user@example.com).";
            //     regEmail.focus();
            //     return;
            // }
            
            const formData = new FormData(form);
            const formType = form.querySelector('.submit-btn').getAttribute('data-form-type');
            formData.append('form_type', formType);

            fetch(form.action, {
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
                console.log(data.success);
                showMessage(data.message, data.success);
                if(data.success){
                    if(data.location) window.location.href = "../pattern.php"; 
                    toggle_auth_forms('login');
                }
                //Если ответ от сервера пришел отрицательным
                else{
                    errHandler(data.type);
                }
            })
            .catch(err => {
                console.log('Ошибка при отправке запроса: ', err);
                showMessage('Ошибка соединения с сервером',false);
            })
        });
    })

    //Проверка почты 
    function errHandler(type){
        
        switch(type){
            case 'email':
                break;
            case 'passwd':        
                break;
            default:
                break;
        }
        
    }

    function validateEmail(){
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(regEmail.value);
    }

    //Проверка пароля
    function identityPassword (){
        if (passwd.value !== passwdConfirm.value ){
            warningPassw.textContent = "Пароли не совпадают";
            return false;
        }
        else {
            warningPassw.textContent = "";
            return true;
        }
    }

    //Совпадение паролей
    passwdConfirm.addEventListener('keyup', identityPassword); 


    //Показ сообщения под панелью навигации
    function showMessage (text, isSuccess) {
        message.textContent = text;
        message.className = isSuccess ? "show success" : "show error";

        clearTimeout(message.show);
        clearTimeout(message.style);

        message.show = setTimeout(() => {
            message.classList.remove('show');

            message.style = setTimeout(() =>{
                message.classList.remove('success', 'error');
            }, 300);
        }, 3000);

    }

    
    // Переключение между формами
    if(switchForms){
        switchForms.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                if (loginForm.style.display === 'none') {
                    toggle_auth_forms('login')
                } else {
                    toggle_auth_forms('register')
                }
            });
        });
    }

    // Переключение форм
    function toggle_auth_forms(formType){
        authModal.style.display = 'grid';
        warningPassw.textContent =""

        switch(formType){
            case 'login':
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
                formRegister.reset();
                //Говнокодиеще
                document.getElementById('reviewer-interests').value = '';
                break;
            case 'register':        
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
                formLogin.reset();
                break;
            case 'main':        
                loginForm.style.display = 'none';
                registerForm.style.display = 'none';
                formLogin.reset();
                formRegister.reset();
                authModal.style.display = 'none';
                //Говнокодиеще
                document.getElementById('reviewer-interests').value = '';
            break;
            default:
                console.error("Неправильный тип формы");
                break;
        }
            
    }
  
    // Показать форму входа
     if (loginBtn){
        loginBtn.addEventListener('click', function() {
            toggle_auth_forms('login');
        });
    }
      
    // Показать форму регистрации
    if (registerBtn) {
        registerBtn.addEventListener('click', function() {
            toggle_auth_forms('register');
        });
    }


    
    // Закрыть модальное окно
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            toggle_auth_forms('main');
        });
    }

    


    // Функция для генерации code_verifier
    function generateCodeVerifier(length=64) {
        const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        return result;
    }
    
    async function generateCodeChallenge(codeVerifier) {
    try {
        // Проверка ввода
        if (typeof codeVerifier !== 'string') {
            throw new Error('codeVerifier must be a string');
        }

        // Проверка поддержки Web Crypto API
        if (!window.crypto?.subtle?.digest) {
            throw new Error('Web Crypto API not available');
        }

        // Шаг 1: Кодирование в бинарный формат
        const encoder = new TextEncoder();
        const data = encoder.encode(codeVerifier);

        // Шаг 2: Хеширование (SHA-256)
        const digest = await crypto.subtle.digest('SHA-256', data);

        // Шаг 3: Конвертация в Base64URL
        const byteArray = new Uint8Array(digest);
        const base64 = btoa(String.fromCharCode.apply(null, byteArray));
        const base64Url = base64
            .replace(/\+/g, '-')
            .replace(/\//g, '_')
            .replace(/=+$/, '');

        return base64Url;
        } catch (error) {
            console.error('Failed to generate code challenge:', error);
            throw error; // Можно заменить на return null или другое значение по умолчанию
        }
    }

    
    async function handleOAuthLogin(provider) {
        try {
            if (!oauthProviders[provider]) {
                throw new Error(`Провайдер ${provider} не поддерживается`);
            }

            const config = oauthProviders[provider];
        
            // Генерация PKCE параметров
            const codeVerifier = generateCodeVerifier();
            const codeChallenge = await generateCodeChallenge(codeVerifier);
            const state = generateCodeVerifier(16);
        
            // Сохраняем параметры в sessionStorage
            sessionStorage.setItem(`${provider}_code_verifier`, codeVerifier);
            sessionStorage.setItem(`${provider}_auth_state`, state);
        
        // Параметры авторизации
            const params = new URLSearchParams({
                response_type: 'code',
                client_id: config.clientId,
                scope: config.scope,
                redirect_uri: config.redirectUri,
                state: state,
                code_challenge: codeChallenge,
                code_challenge_method: config.codeChallengeMethod 
             });
        
            // Перенаправление на провайдера
            window.location.href = `${config.authUrl}?${params.toString()}`;
        } catch (error) {
            document.getElementById('errorMsg').textContent = `Ошибка при авторизации через ${provider}: ${error.message}`;
            console.error(error);
        }
    }

    
    //Обрабатываем нажатие кнопок oauth
    document.querySelectorAll('.oauth-btn').forEach(button => {
        button.addEventListener('click', function() {
            const provider = this.classList.contains('oauth-vk') ? 'vk' :
                            this.classList.contains('oauth-github') ? 'github' :
                            this.classList.contains('oauth-yandex') ? 'yandex' : null;
            if (provider) {
            handleOAuthLogin(provider);
            }
        });
    });

    function showMessageIn(type, message) {
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

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    forgotPasswordForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearMessages();
        displayInputError(emailInput, emailError, '');
        submitEmailButton.disabled = true;

        const email = emailInput.value.trim();

        if (!email) {
            displayInputError(emailInput, emailError, 'Email не может быть пустым.');
            submitEmailButton.disabled = false;
            return;
        }
        if (!isValidEmail(email)) {
            displayInputError(emailInput, emailError, 'Введите корректный email.');
            submitEmailButton.disabled = false;
            return;
        }

        showMessage('info', 'Отправка запроса...');

        try {
            const response = await fetch('/api/auth/forgot_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email: email })
            });

            const data = await response.json();

            // Вне зависимости от успеха, показываем "мягкое" сообщение
            // для безопасности (чтобы не раскрывать, существует ли email)
            showMessage('success', data.message || 'Если ваш email зарегистрирован, вам будет отправлена инструкция по сбросу пароля.');
            emailInput.value = ''; // Очистить поле после отправки

        } catch (error) {
            console.error('Ошибка при запросе сброса пароля:', error);
            showMessage('error', 'Произошла ошибка сети или сервера. Пожалуйста, попробуйте позже.');
        } finally {
            submitEmailButton.disabled = false;
        }
    });


    
    // Закрытие модального окна при клике вне его
    window.addEventListener('click', function(e) {
        if (e.target === authModal) {
            toggle_auth_forms('main');
        }
    });

    // async function handleLogout() {
    //     try {
    //         const response = await fetch('api/user/logout.php');
    //         if (!response.ok) throw new Error('Logout failed');
    //         await updateAuthUI();
    //     } catch (error) {
    //         console.error('Logout error:', error);
    //     }
    // }

    // if(logoutButton){
    //     logoutButton.addEventListener('click', handleLogout);
    // }
});
    
   