/**
 * @file
 * Hide the alert banner, and set a cookie that matches the token.
 *
 * This is so if the alert changes, the banner is reshown.
 */

(function() {

  'use strict';

  function setAlertBannerToHide(tokens, token) {
    tokens.push(token);
    var new_tokens = tokens.join('+')
    localStorage.setItem('hide-alert-banner-token', new_tokens);
  }

  Drupal.behaviors.alertBanner = {
    attach: function () {
      var hidden_tokens = localStorage.getItem('hide-alert-banner-token');
      var hidden_tokens_array = hidden_tokens !== null ? hidden_tokens.split('+') : [];

      const banners = document.querySelectorAll('.js-localgov-alert-banner');
      banners.forEach(banner => {
        banner.classList.remove('hidden');
        var token = banner.getAttribute('data-dismiss-alert-token');

        if (hidden_tokens_array.indexOf(token) > -1) {
          banner.style.display = 'none';
        }
      });

      const buttons = document.querySelectorAll('.js-localgov-alert-banner__close');
      buttons.forEach(button => {
        button.addEventListener('click', (e) => {
          e.preventDefault();

          var banner = button.closest('.js-localgov-alert-banner');
          banner.setAttribute('aria-hidden', 'true');
          banner.style.display = 'none';

          setAlertBannerToHide(hidden_tokens_array, banner.getAttribute('data-dismiss-alert-token'));
        });
      });
    }
  };

})();
