<?php

namespace Drupal\group_alert_banner\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Group Alert banner routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.group_relationship.create_page')) {
      $copy = clone $route;
      $copy->setPath('group/{group}/alert-banner/create');
      $copy->setDefault('base_plugin_id', 'group_localgov_alert_banner');
      $collection->add('entity.group_relationship.group_alert_banner_create_banner', $copy);
    }

    if ($route = $collection->get('entity.group_relationship.add_page')) {
      $copy = clone $route;
      $copy->setPath('group/{group}/alert-banner/add');
      $copy->setDefault('base_plugin_id', 'group_localgov_alert_banner');
      $collection->add('entity.group_relationship.group_alert_banner_add_banner', $copy);
    }
  }

}
