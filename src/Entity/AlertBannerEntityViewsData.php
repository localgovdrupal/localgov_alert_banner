<?php

namespace Drupal\localgov_alert_banner\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Alert banner entities.
 */
class AlertBannerEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
