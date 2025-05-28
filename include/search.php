<div class="search-form">
    <form id="searchForm">
        <div class="search-row">
            <input type="text" id="searchTitle" placeholder="Название статьи">
            <input type="text" id="searchAuthor" placeholder="Автор">
             <select id="searchStatus">
                <option value="">Все статусы</option>
                <option value="submitted">На рассмотрении</option>
                <option value="under_review">На рецензии</option>
                <option value="review_completed">Рецензия готова</option>
                <option value="revision_requested">Требуются правки</option>
                <option value="accepted">Принята</option>
                <option value="published">Опубликована</option>
                <option value="rejected">Отклонена</option>
            </select>
            <button type="submit" class="search-btn">Поиск</button>
            <button type="button" id="resetSearch" class="reset-btn">Сбросить</button>
        </div>
    </form>
</div>