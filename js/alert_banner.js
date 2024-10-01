/**
 * @file
 * Hide the alert banner, and set a cookie that matches the token.
 *
 * This is so if the alert changes, the banner is reshown.
 */

(function($) {

  'use strict';

  function setAlertBannerHideCookie(cookie_tokens, token) {
    cookie_tokens.push(token);
    const new_cookie = cookie_tokens.join('+');
    // Set expiry 30 days.
    const expiry = Date.now() + (30 * 24 * 60 * 60 * 1000);
    document.cookie = 'hide-alert-banner-token=' + new_cookie + '; expires=' + new Date(expiry).toString() + '; SameSite=Lax;'
  }

  $(document).ready(function() {

    const all_cookies = document.cookie.split('; ');
    for (let i = 0; i < all_cookies.length; i++) {
      const indv_cookie = all_cookies[i].split('=');
      if (indv_cookie[0] == 'hide-alert-banner-token') {
        const cookie = indv_cookie[1];
      }
    }
    const cookie_tokens = typeof cookie !== 'undefined' ? cookie.split('+') : [];

    $('.js-localgov-alert-banner').each(function() {
      $(this).removeClass('hidden');
      const token = $(this).data('dismiss-alert-token');
      if ($.inArray(token, cookie_tokens) > -1) {
        $(this).hide();
      }
    });

    $('.js-localgov-alert-banner__close').click(function(e) {
      e.preventDefault();
      const banner = $(this).closest('.js-localgov-alert-banner');
      banner.attr("aria-hidden", "true").slideUp('fast');
      setAlertBannerHideCookie(cookie_tokens, banner.data('dismiss-alert-token'));
    });

  });

}) (jQuery);
