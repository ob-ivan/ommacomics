document.querySelectorAll('.chapter-list__action').forEach((action: HTMLElement) =>
    action.addEventListener('click', event => {
        if (!window.confirm(action.dataset.confirmation)) {
            event.preventDefault();
            event.stopPropagation();
        }
    })
);
