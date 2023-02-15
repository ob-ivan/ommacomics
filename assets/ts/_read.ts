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

    setImageStyle();
};
