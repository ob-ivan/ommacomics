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

const readImageHorizontalList = document.querySelectorAll('.read__image--horizontal');
let readImageHorizontalVisibleIndex = 1;
readImageHorizontalList.forEach((target: HTMLElement) =>
    target.addEventListener('click', event => {
        readImageHorizontalVisibleIndex = parseInt(target.dataset.index) + 1;
        readImageHorizontalList.forEach((next: HTMLElement) => {
            next.style.left = `${(parseInt(next.dataset.index) - readImageHorizontalVisibleIndex) * 100}%`
        })
    })
);
