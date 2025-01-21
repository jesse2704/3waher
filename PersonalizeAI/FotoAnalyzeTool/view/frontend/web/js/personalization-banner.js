require(['jquery', 'mage/cookies'], function($) {
    $(document).ready(function() {
        var personalizationBanner = $('#personalizationBanner');
        var cookieValue = $.mage.cookies.get('personalizationToggler');
        console.log('Cookie value:', cookieValue);
        
        if (cookieValue === 'true') {
            console.log('Showing banner');
            personalizationBanner.show();
        } else {
            console.log('Hiding banner');
            personalizationBanner.hide();
        }
    });
});
