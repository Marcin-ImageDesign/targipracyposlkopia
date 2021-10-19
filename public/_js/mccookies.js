function MCCreateCookie(name, value, days) {
    var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    var expires = "; expires=" + date.toGMTString();
    document.cookie = name + "=" + value + expires + "; path=/";
}

function MCReadCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

window.onload = MCCheckCookies;

function MCCheckCookies() {
    if (MCReadCookie(cookie_voxexpo) != 'true') {
        var message_container = document.createElement('div');
        message_container.id = 'cookies-message-container';
        message_container.innerHTML = '<div id="cookies-message" style="vertical-align: center;font-family:Verdana;font-style: normal;font-variant: normal;font-weight: normal;padding: 10px 0; font-size: 12px; line-height: 22px; border-top: 1px solid #999999; text-align: center; position: fixed; bottom: 0; background-color: #dedede;color:#666666; width: 100%; z-index: 999;">Ta strona używa COOKIES. Korzystając z niej wyrażasz zgodę na wykorzystywanie cookies, zgodnie z ustawieniami Twojej przeglądarki. <a href="javascript:MCCloseCookiesWindow();" id="accept-cookies-checkbox" name="accept-cookies" style="background-color: #666666; padding: 5px 10px; color: #FFF; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px; display: inline-block; margin-left: 10px; text-decoration: none; cursor: pointer;">OK, zamknij</a></div>';

        document.body.appendChild(message_container);
    }
}

function MCCloseCookiesWindow() {
    MCCreateCookie(cookie_voxexpo, 'true', 365);
    document.getElementById('cookies-message-container').removeChild(document.getElementById('cookies-message'));
}
