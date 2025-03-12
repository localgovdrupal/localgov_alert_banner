/**
 * @file
 * Present a full page alert banner as a dialog.
 *
 * The alert banner is added by the localgov_alert_banner module.
 *
 * @see localgov_alert_banner_preprocess_localgov_alert_banner__full()
 * @see localgov-alert-banner--full.html.twig
 */

(function launchModalAlertBanner(Drupal, drupalSettings, cookieStore) {
  Drupal.behaviors.launchModalAlertBanner = {
    attach: function attach() {
      const alertId =
        drupalSettings.localgov_alert_banner_full_page
          .localgov_full_page_alert_banner_id;

      const lgAlert = document.getElementById(alertId);
      if (lgAlert === null) {
        return;
      }

      if (this.isHiddenAlert(lgAlert)) {
        return;
      }

      if (window.dialogPolyfill) {
        window.dialogPolyfill.registerDialog(lgAlert);
      }

      const cancelButton = document.getElementById(`${alertId}-canceloverlay`);

      cancelButton.addEventListener('click', function closeAlert() {
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
    isHiddenAlert(lgAlert) {
      const dismissToken = lgAlert.getAttribute('data-dismiss-alert-token');
      const isHidden = cookieStore
        .split(';')
        .some(
          (item) =>
            item.trim().startsWith('hide-alert-banner-token=') &&
            item.includes(dismissToken),
        );
      return isHidden;
    },
  };
})(Drupal, drupalSettings, document.cookie);
