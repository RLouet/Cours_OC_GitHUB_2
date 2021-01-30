$(document).ready(function() {
    $('#confirmCookiesBtn').click(function () {
        let date = new Date(Date.now() + 365 * 24 * 60 * 60 * 1000);
        date = date.toUTCString();

        document.cookie = 'accept_cookies=true; path=/; expires=' + date;

        $('#confirmCookiesContainer').remove();
    })
});