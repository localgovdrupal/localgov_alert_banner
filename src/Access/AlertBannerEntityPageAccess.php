<?php

namespace Drupal\localgov_alert_banner\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Checks access to alert banner entity pages.
 *
 * @ingroup localgov_alert_banner
 */
class AlertBannerEntityPageAccess implements AccessInterface {

  /**
   * {@inheritDoc}
   */
  public function access(AccountInterface $account) {

    if ($account->hasPermission('view all localgov alert banner entity pages')) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

}
