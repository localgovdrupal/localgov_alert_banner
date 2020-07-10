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

    $data['localgov_alert_banner']['status_confirm_page'] = [
      'field' => [
        'title' => $this->t('Link to publish or unpublish confirmation page'),
        'help' => $this->t('Provide a simple link to change the status of the alert.'),
        'id' => 'localgov_alert_banner_status_page',
        'click sortable' => FALSE,
      ],
    ];

    return $data;
  }

}
