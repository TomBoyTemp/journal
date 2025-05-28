document.addEventListener('DOMContentLoaded', function() {
    
    // Для главного меню и его элементов
    const navLinksWithSubmenu = document.querySelectorAll(".navigation .link"); // Выбираем только ссылки, которые открывают подменю
    
    // Для реализации выдвижения главного меню
    const iconBtn = document.querySelector(".icon");
    const subheader = document.querySelector(".subheader");
    
    // Брейкпоинт для мобильной версии
    const mobileBreakpoint = 600;

    function isMobile() {
        return window.innerWidth <= mobileBreakpoint;
    }

    // Подменю основного меню
    // Перебираем только ссылки, которые должны открывать подменю
    navLinksWithSubmenu.forEach(link => {
        link.addEventListener('click', function(event) {
            // Находим ближайший родительский элемент <li> с классом navigation
            const parentNavigationItem = link.closest('.navigation');
            if (!parentNavigationItem) return; // На всякий случай, если структура изменится

            const currentSubmenu = parentNavigationItem.querySelector(".submenu");
            if (!currentSubmenu) return; // Если подменю нет, ничего не делаем

            const wasOpen = currentSubmenu.classList.contains('show');
            
            // Предотвращаем стандартное действие только для этой ссылки
            // (чтобы она не переходила по href="#" или другим заглушкам)
            event.preventDefault();
            event.stopPropagation(); // Предотвращаем всплытие, чтобы избежать закрытия от document.addEventListener('click')

            if (isMobile()) {
                currentSubmenu.classList.toggle('show');
            } else {
                // Закрываем все другие открытые подменю на десктопе
                document.querySelectorAll('.submenu.show').forEach(menu => {
                    if (menu !== currentSubmenu) { // Не закрываем текущее подменю
                        menu.classList.remove('show');
                    }
                });
                // Открываем/закрываем текущее подменю
                if (!wasOpen) { // Открываем, если не было открыто
                    currentSubmenu.classList.add('show');
                } else { // Закрываем, если было открыто (при повторном клике)
                    currentSubmenu.classList.remove('show');
                }
            }
        });
    });

    iconBtn.addEventListener('click', function(event){
        event.preventDefault();
        event.stopPropagation(); // Останавливаем всплытие, чтобы не закрывалось сразу
        subheader.classList.toggle('responsive');
    });

    // Закрытие подменю при клике вне его
    document.addEventListener('click', function(e) {
        // Проверяем, был ли клик вне элементов, которые управляют подменю
        // или вне самих подменю
        if (!e.target.closest('.navigation') && !e.target.closest('.submenu') && !e.target.closest('.icon')) {
            document.querySelectorAll('.submenu.show').forEach(menu => {
                menu.classList.remove('show');
            });
            // Если главное меню открыто в мобильной версии и клик вне его
            if (isMobile() && subheader.classList.contains('responsive')) {
                subheader.classList.remove('responsive');
            }
        }
    });
});