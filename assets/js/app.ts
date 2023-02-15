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

const readContainerHorizontal = document.querySelector('.read__container--horizontal');
const readImageHorizontalList = document.querySelectorAll('.read__image--horizontal');
let readImageHorizontalVisibleIndex = 1;
const setReadImageHorizontalStyle = () => readImageHorizontalList.forEach((image: HTMLElement) => {
    const index = parseInt(image.dataset.index);
    image.style.left = index < readImageHorizontalVisibleIndex ? '-100%' : index > readImageHorizontalVisibleIndex ? '100%' : '0';
    image.style.opacity = `${index === readImageHorizontalVisibleIndex ? 1 : 0}`;
});
readContainerHorizontal.addEventListener('click', event => {
    ++readImageHorizontalVisibleIndex;
    if (readImageHorizontalVisibleIndex > readImageHorizontalList.length) {
        readImageHorizontalVisibleIndex = 1;
    }
    setReadImageHorizontalStyle();
});
setReadImageHorizontalStyle();
