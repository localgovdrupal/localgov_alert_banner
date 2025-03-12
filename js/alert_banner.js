/**
 * @file
 * Hide the alert banner, and set a cookie that matches the token.
 *
 * This is so if the alert changes, the banner is reshown.
 */

function setAlertBannerHideCookie(cookieTokens, token) {
  cookieTokens.push(token);
  const newCookie = cookieTokens.join('+');
  // Set expiry 30 days.
  const expiry = Date.now() + 30 * 24 * 60 * 60 * 1000;
  document.cookie = `hide-alert-banner-token=${newCookie}; expires=${new Date(
    expiry,
  ).toUTCString()}; path=/; SameSite=Lax;`;
}

(function localgovAlertBannerScript(Drupal) {
  Drupal.behaviors.localgovAlertBanners = {
    attach(context) {
      const allCookies = document.cookie.split('; ');
      let cookie;

      for (let i = 0; i < allCookies.length; i++) {
        const indvCookie = allCookies[i].split('=');
        if (indvCookie[0] === 'hide-alert-banner-token') {
          [, cookie] = indvCookie;
        }
      }

      const cookieTokens =
        typeof cookie !== 'undefined' ? cookie.split('+') : [];

      const alertBanners = once(
        'allAlertBanners',
        '.js-localgov-alert-banner',
        context,
      );

      if (alertBanners) {
        alertBanners.forEach(function (banner) {
          banner.classList.remove('hidden');
          const token = banner.getAttribute('data-dismiss-alert-token');
          if (cookieTokens.includes(token)) {
            banner.style.display = 'none';
          }
        });
      }

      const alertBannerCloseButtons = once(
        'allAlertBannerCloseButtons',
        '.js-localgov-alert-banner__close',
        context,
      );

      if (alertBannerCloseButtons) {
        alertBannerCloseButtons.forEach(function (closeButton) {
          closeButton.addEventListener('click', function (e) {
            e.preventDefault();
            const banner = closeButton.closest('.js-localgov-alert-banner');
            banner.setAttribute('aria-hidden', 'true');
            banner.style.display = 'none';
            setAlertBannerHideCookie(
              cookieTokens,
              banner.getAttribute('data-dismiss-alert-token'),
            );
          });
        });
      }
    },
  };
})(Drupal);
