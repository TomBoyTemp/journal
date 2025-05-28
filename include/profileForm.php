    <?php
                        // Проверка авторизации пользователя
                        if (isset($_SESSION['user'])) {
                            $user = $_SESSION['user'];
                            

                            $roles = is_array($user['role']) ? $user['role'] : [$user['role']];

                            $isAuthor = in_array('author', $roles);
                            $isReviewer = in_array('reviewer', $roles);
                            $isEditor = in_array('editor', $roles);
                            
                            // Генерация инициалов для аватара
                             $initials = '';
                            $fields = ['first_name', 'last_name']; // можно добавить отчество
                            
                            foreach ($fields as $field) {
                                if (!empty($user[$field])) {
                                    $initials .= mb_substr($user[$field], 0, 1);
                                }
                            }
                            ?>
                            <div class="profile-card">
                                <div class="profile-header">
                                    <div class="profile-avatar"><?= $initials?></div>
                                    <div class="profile-info">
                                        <h3><?= htmlspecialchars($user['first_name']) ?></h3>
                                        <p>
                                            <?php
                                            $roles = [];
                                            if ($isAuthor) $roles[] = 'Автор';
                                            if ($isReviewer) $roles[] = 'Рецензент';
                                            if ($isEditor) $roles[] = 'Редактор';
                                            echo implode(', ', $roles);
                                            ?>
                                        </p>
                                    </div>
                                </div>
                                <?php if ($isEditor): ?>
                                    <a href="/create_issue.php" class="profile-action">
                                        <i class="fas fa-book"></i>
                                        <span>Управление выпусками</span>
                                    </a>
                                    <a href="/editor_dashboard.php" class="profile-action">
                                        <i class="fas fa-tasks"></i>
                                        <span>Статьи на рассмотрении</span>
                                    </a>
                                    <a href="/manage_reviewers.php" class="profile-action">
                                        <i class="fas fa-user-shield"></i>
                                        <span>Управление рецензентами</span>
                                    </a>
                                <?php endif; ?>
                                
                                <div class="profile-actions">
                                    <?php if ($isAuthor): ?>
                                        <a href="/my_articles.php" class="profile-action">
                                            <i class="fas fa-file-alt"></i>
                                            <span>Мои статьи</span>
                                        </a>
                                        <a href="/submit_article.php" class="profile-action">
                                            <i class="fas fa-upload"></i>
                                            <span>Подать статью</span>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($isReviewer): ?>
                                        <a href="/reviewer_dashboard.php" class="profile-action">
                                            <i class="fas fa-envelope-open-text"></i>
                                            <span>Мои приглашения</span>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="/logout.php" class="profile-action">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Выйти</span>
                                    </a>
                                </div>
                            </div>
                            <?php
                        } else {
                            // Блок для неавторизованных пользователей
                            ?>
                            <div class="profile-card">
                                <div class="profile-header">
                                    <div class="profile-avatar"><i class="fas fa-user"></i></div>
                                    <div class="profile-info">
                                        <h3>Гость</h3>
                                        <p>Не авторизован</p>
                                    </div>
                                </div>
                                
                                <div class="profile-actions">
                                    <button id="openLoginModalBtn" class="profile-action">
                                        <i class="fas fa-sign-in-alt"></i>
                                        <span>Вход</span>
                                    </button>
                                    <button id="openRegisterModalBtn" class="profile-action">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Регистрация</span>
                                    </button>
                                </div>
                            </div>
                            <?php
                        }
                        ?>