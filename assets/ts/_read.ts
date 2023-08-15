export const ReadHorizontal = () => {
    const imageList = document.querySelectorAll('.read__image--horizontal');
    let currentIndex = 1;
    const setImageStyle = () => imageList.forEach((image: HTMLElement) => {
        const index = parseInt(image.dataset.index);
        image.style.left = index < currentIndex ? '-100%' : index > currentIndex ? '100%' : '0';
        image.style.opacity = `${index === currentIndex ? 1 : 0}`;
    });
    const movePrev = () => {
        currentIndex = Math.max(currentIndex - 1, 1);
        setImageStyle();
    };
    const moveNext = () => {
        currentIndex = Math.min(currentIndex + 1, imageList.length);
        setImageStyle();
    };

    const buttonPrev = document.querySelector('.read__button--prev');
    const buttonNext = document.querySelector('.read__button--next');
    buttonPrev.addEventListener('click', movePrev);
    buttonNext.addEventListener('click', moveNext);

    const container = document.querySelector('.read__container--horizontal');
    let touchCount = 0;
    let touchScrollEnabled = true;
    let touchstartX = 0;
    let touchstartY = 0;
    container.addEventListener('touchstart', function(event: TouchEvent) {
        ++touchCount;
        if (touchCount >= 2) {
            touchScrollEnabled = false;
        }

        if (touchScrollEnabled) {
            touchstartX = event.changedTouches[0].screenX;
            touchstartY = event.changedTouches[0].screenY;
        }
    }, false);
    container.addEventListener('touchend', function(event: TouchEvent) {
        --touchCount;
        if (!touchScrollEnabled) {
            if (touchCount === 0) {
                touchScrollEnabled = true;
            }

            return;
        }

        const touchendX = event.changedTouches[0].screenX;
        const touchendY = event.changedTouches[0].screenY;
        const dx = touchendX - touchstartX;
        const dy = touchendY - touchstartY;
        if (Math.abs(dx) > 10) {
            const tan = dy / dx;
            if (Math.abs(tan) < 1) {
                if (dx > 0) {
                    movePrev();
                } else {
                    moveNext();
                }
            }
        }
    }, false);

    setImageStyle();
};
