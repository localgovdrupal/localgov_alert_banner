<?php
/**
 * @file
 * Access check for Alert Banner entity page
 */

namespace Drupal\localgov_alert_banner\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

class AlertBannerEntityPageAccess implements AccessInterface {

  public function access(AccountInterface $account) {

    if ($account->hasPermission('view all localgov alert banner entity pages')) {
      return AccessResult::allowed();
    } else {
      return AccessResult::forbidden();
    }
  }
}
