<?php
/**
 * Alert Banner Controller
 * @file
 *  Defines a default controller for editing the Alert Banner
 */

namespace Drupal\bhcc_alert_banners\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\eck\Entity\EckEntity;

/**
 * Class AlertBannerController.
 */
class AlertBannerController extends ControllerBase {

  /**
   * Edit
   *
   * Edit the default alert banner
   */
  public function edit() {

    // Find if there is an existing alert banner.
    $defaultAlertBannerId = $this->getDefaultAlertBanner();

    if (!empty($defaultAlertBannerId)) {
      // If there is, show the edit for for that.
      $alertBanner = EckEntity::load($defaultAlertBannerId);
    } else {
      // If not, show the add new banner form
      $alertBanner = EckEntity::create(['type' => 'alert_banner']);
    }

    // Return the edit form.
    return \Drupal::service('entity.form_builder')->getForm($alertBanner);

  }

  /**
   * Get the deafult alert banner
   * @return int The first alert banner found, or null if not.
   */
  private function getDefaultAlertBanner() {

    $query = $this->entityTypeManager()
      ->getStorage('alert_banner')
      ->getQuery()
      ->execute();

    $defaultAlertBannerId = reset($query);
    return $defaultAlertBannerId;
  }

}
