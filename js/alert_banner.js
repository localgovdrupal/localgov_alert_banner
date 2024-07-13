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
    var new_cookie = cookie_tokens.join('+');
    // Set expiry 30 days.
    var expiry = Date.now() + (30 * 24 * 60 * 60 * 1000);
    document.cookie = 'hide-alert-banner-token=' + new_cookie + '; expires=' + new Date(expiry).toString() + '; SameSite=Lax;'
  }

  $(document).ready(function() {

    var all_cookies = document.cookie.split(';');
    for (var i = 0; i < all_cookies.length; i++) {
      var indv_cookie = all_cookies[i].split('=');
      if (indv_cookie[0] == 'hide-alert-banner-token') {
        var cookie = indv_cookie[1];
      }
    }
    var cookie_tokens = typeof cookie !== 'undefined' ? cookie.split('+') : [];

    $('.js-localgov-alert-banner').each(function() {
      $(this).removeClass('hidden');
      var token = $(this).data('dismiss-alert-token');
      if ($.inArray(token, cookie_tokens) > -1) {
        $(this).hide();
      }
    });

    $('.js-localgov-alert-banner__close').click(function(e) {
      e.preventDefault();
      var banner = $(this).closest('.js-localgov-alert-banner');
      banner.attr("aria-hidden", "true").slideUp('fast');
      setAlertBannerHideCookie(cookie_tokens, banner.data('dismiss-alert-token'));
    });

  });

}) (jQuery);
