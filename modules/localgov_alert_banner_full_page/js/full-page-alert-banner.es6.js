/**
 * @file
 * Present a full page alert banner as a Bootstrap modal.
 *
 * The alert banner is added by the localgov_alert_banner module.
 *
 * @see localgov_alert_banner_preprocess_localgov_alert_banner__full()
 * @see localgov-alert-banner--full.html.twig
 */

(function launchModalAlertBanner(Drupal, drupalSettings) {
  Drupal.behaviors.launchModalAlertBanner = {
    attach: function attach() {

      const lgAlert = document.getElementById('lgAlert');

      if (window.dialogPolyfill) {
        dialogPolyfill.registerDialog(lgAlert);
      }

      const cancelButton = document.getElementById('canceloverlay');

      cancelButton.addEventListener('click', function() {
        lgAlert.close();
        localStorage.setItem("overlayonce", "true");
      });

      if(!localStorage.getItem("overlayonce")) {
        lgAlert.showModal();
      }
    }
  };
})(Drupal, drupalSettings);
