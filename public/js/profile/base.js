'use strict';

(function () {
    const $document = $(document);
    $document.ready(function(){
        $('.btn-menu').click(function(){
            $('.left-side').toggleClass('active');
            $('.main-content').toggleClass('active');
            return false;
        });
        $document.click(function() {
            $('.left-side.active').removeClass('active');
        });
    });
}());
