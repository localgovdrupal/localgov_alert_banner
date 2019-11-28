<?php
/**
 * @file
 * Route subscriber for alert banner
 */

namespace Drupal\bhcc_alert_banner\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class AlertBannerRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    if ($route = $collection->get('entity.alert_banner.canonical')) {
      // Change the access permission for the alert banner access page
      $route->setRequirement('_custom_access', 'bhcc_alert_banner.alert_banner_entity_page_access::access');
    }

  }
}
