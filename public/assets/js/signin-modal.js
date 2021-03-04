$(document).ready(function () {
    if (window.location.href.indexOf('#registration') != -1) {
        $('#registration').modal('show');
    }
});

$(document).ready(function () {
    if (window.location.href.indexOf('#login') != -1) {
        $('#login').modal('show');
    }
});


const extendButton = document.getElementById('extend-button');
const extendButtonIcon = document.getElementById('extend-button-icon');


console.log(extendButton);
extendButton.addEventListener('click', (event) => {
    if (extendButtonIcon.classList.contains('fa-plus')) {
        extendButtonIcon.classList.replace('fa-plus', 'fa-minus');
    } else {
        extendButtonIcon.classList.replace('fa-minus', 'fa-plus');
    }

})

const extendButton2 = document.getElementById('extend-button2');
const extendButton2Icon = document.getElementById('extend-button2-icon');

console.log(extendButton2);
extendButton2.addEventListener('click', (event) => {
    console.log("yes")
    if (extendButton2Icon.classList.contains('fa-plus')) {
        extendButton2Icon.classList.replace('fa-plus', 'fa-minus');
    } else {
        extendButton2Icon.classList.replace('fa-minus', 'fa-plus');
    }

})




