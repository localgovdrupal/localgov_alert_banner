<?php

namespace Drupal\localgov_alert_banner;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntityType;

/**
 * Provides dynamic permissions for Alert banner of different types.
 *
 * @ingroup localgov_alert_banner
 */
class AlertBannerEntityPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of node type permissions.
   *
   * @return array
   *   The AlertBannerEntity by bundle permissions.
   *   @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  public function generatePermissions() {
    $perms = [];

    foreach (AlertBannerEntityType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }

    return $perms;
  }

  /**
   * Returns a list of node permissions for a given node type.
   *
   * @param \Drupal\localgov_alert_banner\Entity\AlertBannerEntityType $type
   *   The AlertBannerEntity type.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildPermissions(AlertBannerEntityType $type) {
    $type_id = $type->id();
    $type_params = ['%type_name' => $type->label()];

    return [
      // View alert banner entities of type.
      "view localgov alert banner $type_id entities" => [
        'title' => $this->t('View alert banner entities of type %type_name', $type_params),
      ],

      // Manage the alert banner of type (full CRUD permissions).
      "manage localgov alert banner $type_id entities" => [
        'title' => $this->t('Manage alert banner entities of type %type_name', $type_params),
      ],
    ];
  }

}
