/**
 * @file
 * Hide the alert banner, and set a cookie that matches the token.
 *
 * This is so if the alert changes, the banner is reshown.
 */

(function($, Drupal, drupalSettings) {

  'use strict';

  function setAlertBannerHideCookie(token) {
    $.cookie('hide-alert-banner-token', token, { path: '/', expires: 30 });
  }

  $(document).ready(function() {

    var token = drupalSettings.localgov_alert_banner.token;
    var cookie = $.cookie('hide-alert-banner-token');

    $('.js-alert-banner').removeClass('hidden');
    // Hide if cookie matches token
    if (cookie == token) {
      $('[data-dismiss-alert-token="'+ cookie +'"]').hide();
    }

    $('.js-alert-banner-close').click(function(e) {
      e.preventDefault();
      $(this).closest('.js-alert-banner').slideUp('fast');
      setAlertBannerHideCookie(token);
    });

  });

}) (jQuery, Drupal, drupalSettings);
