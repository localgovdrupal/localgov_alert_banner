/**
 * @file
 * Hide the alert banner, and set a cookie that matches the token.
 *
 * This is so if the alert changes, the banner is reshown.
 */

(function(Drupal, $, cookies) {

  'use strict';

  function setAlertBannerHideCookie(cookie_tokens, token) {
    console.log('test');
    cookie_tokens.push(token);
    var new_cookie = cookie_tokens.join('+')
    cookies.set('hide-alert-banner-token', new_cookie, { path: '/', expires: 30, SameSite: 'Lax' });
  }

  Drupal.behaviors.localgov_alert_banner = {
    attach: function (context, settings) {
      var cookie = cookies.get('hide-alert-banner-token');
      var cookie_tokens = typeof cookie !== 'undefined' ? cookie.split('+') : [];

      $('.js-localgov-alert-banner__close').click(function(e) {
        e.preventDefault();
        var banner = $(this).closest('.js-localgov-alert-banner');
        banner.attr("aria-hidden", "true").slideUp('fast');
        setAlertBannerHideCookie(cookie_tokens, banner.data('dismiss-alert-token'));
      });
    }
  };

}) (Drupal, jQuery, window.Cookies);
