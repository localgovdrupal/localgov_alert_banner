/**
 * @file
 * Hide the alert banner, and set a cookie that matches the token.
 *
 * This is so if the alert changes, the banner is reshown.
 */

(function($, Drupal, drupalSettings) {

  'use strict';

  function setAlertBannerHideCookie(cookie_tokens, token) {
    cookie_tokens.push(token);
    var new_cookie = cookie_tokens.join('+')
    $.cookie('hide-alert-banner-token', new_cookie, { path: '/', expires: 30 });
  }

  $(document).ready(function() {

    var cookie = $.cookie('hide-alert-banner-token');
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

}) (jQuery, Drupal, drupalSettings);
