// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');
require('./_chapter_list');

const ReadHorizontal = () => {
    const buttonPrev = document.querySelector('.read__button--prev');
    const buttonNext = document.querySelector('.read__button--next');
    const imageList = document.querySelectorAll('.read__image--horizontal');
    let currentIndex = 1;
    const setImageStyle = () => imageList.forEach((image: HTMLElement) => {
        const index = parseInt(image.dataset.index);
        image.style.left = index < currentIndex ? '-100%' : index > currentIndex ? '100%' : '0';
        image.style.opacity = `${index === currentIndex ? 1 : 0}`;
    });
    buttonPrev.addEventListener('click', () => {
        currentIndex = Math.max(currentIndex - 1, 1);
        setImageStyle();
    });
    buttonNext.addEventListener('click', () => {
        currentIndex = Math.min(currentIndex + 1, imageList.length);
        setImageStyle();
    });
    setImageStyle();
};
ReadHorizontal();
