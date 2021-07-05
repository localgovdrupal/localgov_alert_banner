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
(function launchModalAlertBanner(Drupal, drupalSettings) {
  Drupal.behaviors.launchModalAlertBanner = {
    attach: function attach() {

      const cancelButton = document.getElementById('canceloverlay');

      cancelButton.addEventListener('click', function() {
        favDialog.close();
        localStorage.setItem("overlayonce", "true");
        document.activeElement.blur();
      });

      if(!localStorage.getItem("overlayonce")) {
        favDialog.showModal();
        //document.body.append('<a href="" id="updateDetails">hello</a>');
      }
    }
  };
})(Drupal, drupalSettings); // eslint-disable-line
