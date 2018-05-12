'use strict';

(function () {
    const $window = $(window);
    const $header = $('header');

    $(function () {
        const hash = window.location.hash;
        if (hash.length === 0) {
            return;
        }
        const $el = $(hash);
        if ($el.length !== 1) {
            return;
        }
        $window.scrollTop($el.offset().top - $header.height() - 50, 400);
    });

    $(function () {
        $('.js-profile-link').click(function () {
            const $el = $($(this).attr('href'));
            scrollTop($el.offset().top - $header.height() - 50, 400);
            return false;
        });

        function scrollTop(to, duration) {
            const start = $window.scrollTop();
            const change = to - start;
            let currentTime = 0;
            const increment = 20;

            const animateScroll = function(){
                currentTime += increment;
                $window.scrollTop(easeInOutQuad(currentTime, start, change, duration));
                if (currentTime < duration) {
                    setTimeout(animateScroll, increment);
                }
            };
            animateScroll();
        }

        const easeInOutQuad = function (t, b, c, d) {
            t /= d/2;
            if (t < 1) {
                return c/2*t*t + b;
            }
            t--;
            return -c/2 * (t*(t-2) - 1) + b;
        };
    });
}());
