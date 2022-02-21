<?php

namespace Drupal\localgov_alert_banner\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Checks access to alert banner entity pages.
 *
 * @ingroup localgov_alert_banner
 */
class AlertBannerEntityPageAccess implements AccessInterface {

  /**
   * {@inheritDoc}
   */
  public function access(AccountInterface $account, RouteMatchInterface $route_match) {

    if ($account->hasPermission('view all localgov alert banner entity pages')) {
      return AccessResult::allowed();
    }
    elseif ($route_match->getParameters()->has('localgov_alert_banner') && $alert_banner = $route_match->getParameter('localgov_alert_banner')) {
      $type_id = $alert_banner->bundle();
      if ($account->hasPermission("view localgov alert banner $type_id pages")) {
        return AccessResult::allowed();
      }
      else {
        return AccessResult::forbidden();
      }
    }
    else {
      return AccessResult::neutral();
    }
  }

}
