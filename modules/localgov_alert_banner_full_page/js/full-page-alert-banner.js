"use strict";

/**
 * @file
 * Present a full page alert banner as a dialog.
 *
 * The alert banner is added by the localgov_alert_banner module.
 *
 * @see localgov_alert_banner_preprocess_localgov_alert_banner__full()
 * @see localgov-alert-banner--full.html.twig
 */
(function launchModalAlertBanner(jQuery, Drupal, drupalSettings) {
  Drupal.behaviors.launchModalAlertBanner = {
    attach: function attach() {
      var alertId = drupalSettings.localgov_alert_banner_full_page.localgov_full_page_alert_banner_id;
      var lgAlert = document.getElementById(alertId);

      if (lgAlert === null) {
        return;
      }

      if (this.isHiddenAlert(lgAlert)) {
        return;
      }

      if (window.dialogPolyfill) {
        window.dialogPolyfill.registerDialog(lgAlert);
      }

      var cancelButton = document.getElementById("".concat(alertId, "-canceloverlay"));
      cancelButton.addEventListener("click", function closeAlert() {
        lgAlert.close();
      });
      lgAlert.showModal();
    },

    /**
     * Is this a hidden alert?
     *
     * @param {object} lgAlert
     *    DOM object.
     *
     * @return {bool}
     *   Is the given alert hidden?
     *
     * @see localgov_alert_banner/js/alert_banner.js
     */
    isHiddenAlert: function isHiddenAlert(lgAlert) {
      var cookie = jQuery.cookie("hide-alert-banner-token");
      var cookieTokens = typeof cookie !== "undefined" ? cookie.split("+") : [];
      var dismissToken = jQuery(lgAlert).data("dismiss-alert-token");
      var isHidden = cookieTokens.includes(dismissToken);
      return isHidden;
    }
  };
})(jQuery, Drupal, drupalSettings);
