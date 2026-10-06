document.addEventListener('DOMContentLoaded', () => {
  const searchForm = document.querySelector('[data-search-form]');
  const searchInput = document.querySelector('[data-search-input]');

  if (searchForm && searchInput) {
    searchForm.addEventListener('submit', (event) => {
      const query = searchInput.value.trim();
      if (!query) {
        return;
      }
      window.location.href = 'attractions.php?search=' + encodeURIComponent(query);
      event.preventDefault();
    });
  }

  const flash = document.querySelector('[data-flash]');
  if (flash) {
    setTimeout(() => {
      flash.classList.add('d-none');
    }, 5000);
  }
});
