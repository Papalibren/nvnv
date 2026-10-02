(function () {
    const SLIDE_DATA = window.SLIDE_DATA || { slides: [], notes: [] };
    let currentIndex = parseInt(new URLSearchParams(location.search).get('s') || '0', 10);

    const stage    = document.getElementById('slide-stage');
    const inner    = document.getElementById('slide-inner');
    const counter  = document.getElementById('slide-counter');
    const progress = document.getElementById('slide-progress');
    const btnPrev  = document.getElementById('btn-prev');
    const btnNext  = document.getElementById('btn-next');
    const notesContent = document.getElementById('notes-content');

    function fitToStage() {
        inner.style.transform = 'scale(1)';
        inner.style.maxHeight = stage.clientHeight + 'px';

        // Даём браузеру пересчитать layout перед измерением
        requestAnimationFrame(() => {
            const naturalHeight = inner.scrollHeight;
            const availableHeight = stage.clientHeight;

            if (naturalHeight > availableHeight) {
                const scale = Math.max(0.62, availableHeight / naturalHeight);
                inner.style.transform = `scale(${scale})`;
            }
        });
    }

    function render() {
        const total = SLIDE_DATA.slides.length;
        const slide = SLIDE_DATA.slides[currentIndex] || '';
        const note  = SLIDE_DATA.notes[currentIndex] || '';

        inner.innerHTML = slide;
        counter.textContent = total ? (currentIndex + 1) + ' / ' + total : '— / —';
        progress.style.width = total ? ((currentIndex + 1) / total * 100) + '%' : '0%';

        notesContent.innerHTML = note
            ? note
            : '<span class="slide-notes-empty">Нет заметок к этому слайду</span>';

        history.replaceState(null, '', '?s=' + currentIndex);

        if (typeof renderMathInElement !== 'undefined') {
            renderMathInElement(inner, {
                delimiters: [
                    { left: '$$', right: '$$', display: true },
                    { left: '$',  right: '$',  display: false },
                ],
                throwOnError: false,
            });
        }
        if (typeof Prism !== 'undefined') {
            Prism.highlightAllUnder(inner);
        }

        btnPrev.disabled = currentIndex === 0;
        btnNext.disabled = currentIndex === total - 1;

        fitToStage();
    }

    function slidePrev() { if (currentIndex > 0) { currentIndex--; render(); } }
    function slideNext() { if (currentIndex < SLIDE_DATA.slides.length - 1) { currentIndex++; render(); } }

    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }

    function toggleNotes() {
        document.getElementById('notes-panel').classList.toggle('hidden');
    }

    document.addEventListener('keydown', (e) => {
        if (['ArrowRight', 'ArrowDown', ' '].includes(e.key)) slideNext();
        if (['ArrowLeft', 'ArrowUp'].includes(e.key)) slidePrev();
        if (e.key === 'f' || e.key === 'F') toggleFullscreen();
        if (e.key === 'n' || e.key === 'N') toggleNotes();
    });

    window.addEventListener('resize', fitToStage);

    btnPrev.addEventListener('click', slidePrev);
    btnNext.addEventListener('click', slideNext);
    document.getElementById('btn-fullscreen').addEventListener('click', toggleFullscreen);
    document.getElementById('btn-notes').addEventListener('click', toggleNotes);

    render();
})();