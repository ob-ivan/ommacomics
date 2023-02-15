/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// var $ = require('jquery');

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');

document.querySelectorAll('.chapter-list__action').forEach((action: HTMLElement) =>
    action.addEventListener('click', event => {
        if (!window.confirm(action.dataset.confirmation)) {
            event.preventDefault();
            event.stopPropagation();
        }
    })
);

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
