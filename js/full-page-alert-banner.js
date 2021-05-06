"use strict";

/**
 * @file
 * Present a full page alert banner as a Bootstrap modal.
 *
 * The alert banner is added by the localgov_alert_banner module.
 *
 * @see localgov_alert_banner_preprocess_localgov_alert_banner__full()
 * @see localgov-alert-banner--full.html.twig
 */
(function launchModalAlertBanner(jQuery, Drupal, drupalSettings, bootstrap) {
  Drupal.behaviors.launchModalAlertBanner = {
    attach: function attach() {
      var modalId = drupalSettings.localgov_alert_banner.localgov_full_page_alert_banner_id;
      var modal = jQuery("#".concat(modalId)).get(0);

      if (typeof modal === "undefined") {
        return;
      } // Display this modal only when the Alert banner module has not hidden it.


      if (this.isHiddenAlert(modal)) {
        return;
      }

      var bsModal = new bootstrap.Modal(modal);
      bsModal.show(); // Attach modal closer as a click event handler of the "Hide" link.

      jQuery(".js-localgov-alert-banner__close", modal).click(function () {
        return bsModal.hide();
      });
    },

    /**
     * Is this a hidden alert?
     *
     * @param {object} modal
     *   jQuery object.
     *
     * @return {bool}
     *   Is the given alert hidden?
     *
     * @see localgov_alert_banner/js/alert_banner.js
     */
    isHiddenAlert: function isHiddenAlert(modal) {
      var cookie = jQuery.cookie("hide-alert-banner-token");
      var cookieTokens = typeof cookie !== "undefined" ? cookie.split("+") : [];
      var dismissToken = jQuery(modal).data("dismiss-alert-token");
      var isHidden = cookieTokens.includes(dismissToken);
      return isHidden;
    }
  };
})(jQuery, Drupal, drupalSettings, bootstrap); // eslint-disable-line
