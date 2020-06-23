<?php

namespace Drupal\localgov_alert_banner\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides access control for the alert banner entity view page.
 *
 * @ingroup localgov_alert_banner
 */
class AlertBannerEntityPageAccess implements AccessInterface {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {

    if ($account->hasPermission('access alert_banner entity page')) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

}
