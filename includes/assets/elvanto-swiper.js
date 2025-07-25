document.addEventListener("DOMContentLoaded", function () {
  var swiper = new Swiper(".swiper-container", {
    slidesPerView: 'auto',
    effect: "cards",
    grabCursor: true,
    cardsEffect: {
    //   slideShadows: false,
    //   perSlideRotate: 0,
      perSlideOffset: 80,
    },
  });
});
