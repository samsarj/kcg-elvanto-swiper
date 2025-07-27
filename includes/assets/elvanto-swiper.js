document.addEventListener("DOMContentLoaded", function () {
  var swiper = new Swiper(".swiper-container", {
    slidesPerView: "auto",
    effect: "cards",
    grabCursor: true,
    mousewheel: {
      enabled: true,
      forceToAxis: true,
    },
    keyboard: {
      enabled: true,
      onlyInViewport: true,
    },
    cardsEffect: {
      perSlideOffset: 80,
    },
  });
});
