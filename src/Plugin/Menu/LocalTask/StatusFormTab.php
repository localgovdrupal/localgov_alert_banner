<?php

namespace Drupal\localgov_alert_banner\Plugin\Menu\LocalTask;

use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\localgov_alert_banner\Controller\AlertBannerEntityController;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Local task plugin to render dynamic tab title dynamically.
 */
class StatusFormTab extends LocalTaskDefault {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getTitle(Request $request = NULL) {
    $alert_banner = $request->attributes->get('localgov_alert_banner');
    if ($alert_banner instanceof AlertBannerEntityInterface) {
      $controller = new AlertBannerEntityController();
      return $controller->getStatusFormTitle($alert_banner);
    }
    return $this->t('Status');
  }

}
