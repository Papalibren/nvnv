// Специфичный JS для админки
document.addEventListener('DOMContentLoaded', () => {
    initPageSorting();
});

function initPageSorting() {
    document.querySelectorAll('.page-sort-list').forEach(list => {
        let draggedItem = null;

        list.querySelectorAll('.page-sort-item').forEach(item => {
            item.addEventListener('dragstart', () => {
                draggedItem = item;
                setTimeout(() => item.style.opacity = '0.4', 0);
            });

            item.addEventListener('dragend', () => {
                item.style.opacity = '1';
                draggedItem = null;
                savePageOrder(list);
            });

            item.addEventListener('dragover', (e) => {
                e.preventDefault();
                const afterElement = getDragAfterElement(list, e.clientY);
                if (!draggedItem) return;

                if (afterElement == null) {
                    list.appendChild(draggedItem);
                } else {
                    list.insertBefore(draggedItem, afterElement);
                }
            });
        });
    });
}

function getDragAfterElement(container, y) {
    const items = [...container.querySelectorAll('.page-sort-item:not([style*="opacity: 0.4"])')];

    return items.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
            return { offset, element: child };
        }
        return closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function savePageOrder(list) {
    const pageIds = [...list.querySelectorAll('.page-sort-item')].map(el => el.dataset.pageId);
    const token = document.querySelector('meta[name="csrf-token"]').content;

    fetch('/admin/content/reorder-pages', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': token,
        },
        body: pageIds.map((id, i) => `page_ids[${i}]=${id}`).join('&'),
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            showSortToast();
        }
    });
}

function showSortToast() {
    let toast = document.getElementById('sort-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'sort-toast';
        toast.className = 'fixed bottom-6 right-6 bg-acid-lime text-white text-sm px-4 py-2 rounded-lg shadow-card-hover z-50';
        toast.textContent = 'Порядок сохранён';
        document.body.appendChild(toast);
    }
    toast.style.opacity = '1';
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => { toast.style.opacity = '0'; }, 1500);
}