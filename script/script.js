const photocarousel = document.querySelector('.photocarousel');
const images =document.querySelectorAll('.photocarousel img');

let index = 0;
const totalImages = images.length;

function defiler (){
    index=index + 1;
    if (index >= totalImages) {
        index =0;
    }
    photocarousel.style.transform = `translateX(-${index * 100}%)`;
}
setInterval (defiler, 3000);


setTimeout (()=>{
    window.alert("Profitez de 10% de reduction des 100€ d'achat");
}, 1500);
