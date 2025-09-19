document.addEventListener("DOMContentLoaded", function () {
  var swiper = new Swiper(".swiper-container", {
    slidesPerView: "auto",
    spaceBetween: 20,
    grabCursor: true,
    mousewheel: {
      enabled: true,
      forceToAxis: true,
    },
    keyboard: {
      enabled: true,
      onlyInViewport: true,
    },
    // Use slide effect instead of cards for better button interaction
    effect: "slide",
    // Center the active slide
    centeredSlides: true,
  });
});
