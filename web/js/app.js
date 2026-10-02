// Глобально добавляем CSRF-токен ко всем HTMX-запросам
document.addEventListener('htmx:configRequest', (event) => {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        event.detail.headers['X-CSRF-Token'] = token.content;
    }
});

document.addEventListener('DOMContentLoaded', () => {
  if (typeof renderMathInElement !== 'undefined') {
    renderMathInElement(document.body, {
      delimiters: [
        { left: '$$', right: '$$', display: true  },
        { left: '$',  right: '$',  display: false },
      ],
      throwOnError: false,
    });
  }
});

document.addEventListener('htmx:afterSwap', (e) => {
  if (typeof renderMathInElement !== 'undefined') {
    renderMathInElement(e.detail.target, {
      delimiters: [
        { left: '$$', right: '$$', display: true  },
        { left: '$',  right: '$',  display: false },
      ],
      throwOnError: false,
    });
  }
});

// Prism autoloader — откуда брать доп. языки
if (typeof Prism !== 'undefined' && Prism.plugins && Prism.plugins.autoloader) {
    Prism.plugins.autoloader.languages_path = 'https://unpkg.com/prismjs@1.29.0/components/';
}

document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('notif-dropdown');
    const btn = document.getElementById('notif-btn');
    if (dropdown && !dropdown.contains(e.target) && e.target !== btn && !btn?.contains(e.target)) {
        dropdown.remove();
    }
});

// Общий хелпер — рендерит KaTeX внутри динамически вставленного HTML
// (для контента вставленного через innerHTML после клика, не через обычную загрузку страницы)
window.renderMathIn = function (el) {
    if (typeof renderMathInElement !== 'undefined' && el) {
        renderMathInElement(el, {
            delimiters: [
                { left: '$$', right: '$$', display: true },
                { left: '$',  right: '$',  display: false },
            ],
            throwOnError: false,
        });
    }
};

function toggleDisclosure(btn) {
    const panel = btn.nextElementSibling;
    const inner = panel.querySelector('.task-disclosure-inner');
    const isOpen = panel.classList.contains('is-open');

    if (isOpen) {
        panel.style.maxHeight = '0px';
        panel.classList.remove('is-open');
        btn.textContent = btn.dataset.show;
    } else {
        panel.style.maxHeight = inner.scrollHeight + 'px';
        panel.classList.add('is-open');
        btn.textContent = btn.dataset.hide;
    }
}