<div class="modal-window" id="modalWindow">
    <div id="message" class="modal-message"></div> <div class="auth-content" id="authContent">
        <span class="close-modal">&times;</span>

        <div class="auth-form" id="loginFormContainer">
            <h2>Вход</h2>
            <form id="loginForm" action="include/autho.php"> <div class="form-group">
                    <label for="loginEmail">Email</label>
                    <input type="email" name="email" id="loginEmail" required> <small class="alertMessage" id="loginEmailError"></small>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Пароль</label>
                    <input type="password" name="password" id="loginPassword" required> <small class="alertMessage" id="loginPasswordError"></small>
                    <div class="forgot-password">
                        <a href="#" id="forgotPasswordLink">Забыли пароль?</a>
                    </div>
                </div>
                <button type="submit" class="submit-btn" data-form-type="login">Войти</button>
            </form>
            <div class="oauth-buttons">
                <p class="oauth-divider">Или войдите через</p>
                <div class="oauth-providers">
                    <button class="oauth-btn oauth-vk">
                        Войти с VK ID
                    </button>
                    <button class="oauth-btn oauth-yandex">
                        GitHub
                    </button>
                    <button class="oauth-btn oauth-yandex">
                        Yandex
                    </button>
                </div>
            </div>
            <p>Нет аккаунта? <a href="#" class="switch-form" data-target="registerFormContainer">Зарегистрироваться</a></p>
        </div>

        <div class="auth-form" id="registerFormContainer" style="display: none;"> <h2>Регистрация</h2>
            <form id="registerForm" action="include/autho.php"> <div class="form-group">
                    <label for="regEmail" class="required">Email</label>
                    <input type="text" id="regEmail" name="email" title="Введите email в формате: user@example.com" required>
                    <small id="regEmailError" class="alertMessage"></small>
                </div>
                <div class="form-group">
                    <label for="regPassword" class="required">Пароль</label>
                    <input type="password" id="regPassword" name="password" required>
                    <small id="regPasswordError" class="alertMessage"></small>
                </div>
                <div class="form-group">
                    <label for="regConfirmPassword">Подтвердите пароль</label>
                    <input type="password" id="regConfirmPassword" required> <small id="regConfirmPasswordError" class="alertMessage"></small>
                </div>
                <div class="form-group">
                    <label for="regName" class="required">Имя</label>
                    <input type="text" id="regName" name="name" required> <small id="regNameError" class="alertMessage"></small>
                </div>
                <div class="form-group">
                    <label for="regSurname" class="required">Фамилия</label>
                    <input type="text" id="regSurname" name="surname" required> <small id="regSurnameError" class="alertMessage"></small>
                </div>
                <div class="form-group">
                    <datalist id="organizations">
                        <option value="Уфимский университет науки и технологий"></option>
                        <option value="Башкирский государственный университет"></option>
                        <option value="Уфимский государственный нефтяной университет"></option>
                        <option value="Башкирский государственный аграрный университет"></option>
                        <option value="Башкирский государственный педагогический университет им. М. Акмуллы"></option>
                        <option value="Башкирская академия государственной службы и управления при Главе Республики Башкортостан"></option>
                        <option value="Башкирский государственный медицинский университет"></option>
                    </datalist>
                    <label for="regOrganization" class="required">Организация</label>
                    <input type="text" id="regOrganization" name="organization" list="organizations" required>
                    <small id="regOrganizationError" class="alertMessage"></small>
                </div>
                <div class="form-group">
                    <label for="regCountry" class="required">Страна</label>
                    <select name="country" id="regCountry" required> <option disabled selected hidden value="">Выберите страну...</option>
                        <option value="AF">Afghanistan</option>
                        <option value="AL">Albania</option>
                        <option value="DZ">Algeria</option>
                        <option value="RU">Russian Federation</option>
                        <option value="US">United States</option>
                        <option value="GB">United Kingdom</option>
                        </select>
                    <small id="regCountryError" class="alertMessage"></small>
                </div>
                <div class="form-associated">
                    <input type="checkbox" id="consentRule" name="consentRule" required>
                    <label for="consentRule" class="required">Даю согласие на сбор и хранение моих данных в соответствии с<a href="http://journal.ugatu.su/index.php/vestnik_bsu/about/privacy" id="rule"> Уведомлением о конфиденциальности. </a></label>
                    <small id="consentRuleError" class="alertMessage"></small>
                </div>
                <div class="form-associated">
                    <input type="checkbox" id="consentEmail" name="consent_email">
                    <label for="consentEmail">Хочу получать уведомления о новых публикациях.</label>
                </div>
                <div class="form-associated">
                    <input type="checkbox" id="isReviewer" name="is_reviewer"> <label for="isReviewer">Хочу чтобы ко мне обращались с запросами на рецензирование материалов для этого журнала.</label>
                </div>
                <div class="toggle-content" id="reviewerInterestsToggle">
                    <label for="reviewerInterests" class="required">Интересы как рецензента</label>
                    <input type="text" id="reviewerInterests" name="reviewer_interests"> <small id="reviewerInterestsError" class="alertMessage"></small>
                </div>
                <button type="submit" class="submit-btn" data-form-type="register">Зарегистрироваться</button>
            </form>
            <p>Уже есть аккаунт? <a href="#" class="switch-form" data-target="loginFormContainer">Войти</a></p>
        </div>

        <div class="auth-form" id="forgotPasswordFormContainer" style="display: none;">
            <h2>Забыли пароль?</h2>
            <p>Пожалуйста, введите ваш email, и мы отправим вам инструкцию по сбросу пароля.</p>
            <div id="forgotPasswordMessages" class="modal-message"></div> <form id="forgotPasswordForm">
                <div class="form-group">
                    <label for="forgotEmail">Email:</label>
                    <input type="email" id="forgotEmail" name="email" required> <small class="alertMessage" id="forgotEmailError"></small>
                </div>
                <button type="submit" id="submitForgotPasswordButton">Отправить</button>
            </form>
            <p><a href="#" class="switch-form" data-target="loginFormContainer">Вернуться к входу</a></p>
        </div>
    </div>
</div>