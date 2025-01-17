document.addEventListener('DOMContentLoaded', function () {
    var swiper = new Swiper('.swiper-container', {
        slidesPerView: 1.5,
        slidesOffsetBefore: 40,
        slidesOffsetAfter: 40,
        spaceBetween: 20,
        grabCursor: true,
        mousewheel: {
            enabled: true,
        },
        keyboard: {
            enabled: true,
            onlyInViewport: false,
          },
        // hashNavigation: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            type: "progressbar",
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
            },
            768: {
                slidesPerView: 3,
            },
            1024: {
                slidesPerView: 4,
            },
        },
    });
});
