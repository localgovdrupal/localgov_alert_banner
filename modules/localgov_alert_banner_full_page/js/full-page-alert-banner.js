/**
 * @file
 * Present a full page alert banner as a dialog.
 *
 * The alert banner is added by the localgov_alert_banner module.
 *
 * @see localgov_alert_banner_preprocess_localgov_alert_banner__full()
 * @see localgov-alert-banner--full.html.twig
 */

(function launchModalAlertBanner(Drupal, drupalSettings) {
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
    isHiddenAlert: function isHiddenAlert(lgAlert) {
      var hidden_tokens = localStorage.getItem('hide-alert-banner-token');
      var hidden_tokens_array = hidden_tokens !== null ? hidden_tokens.split('+') : [];
      var dismissToken = lgAlert.getAttribute("data-dismiss-alert-token");
      var isHidden = hidden_tokens_array.includes(dismissToken);
      return isHidden;
    }
  };
})(Drupal, drupalSettings);