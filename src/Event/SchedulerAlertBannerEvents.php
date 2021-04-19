<?php

namespace Drupal\localgov_alert_banner\Event;

/**
 * Lists the six events dispatched by Scheduler relating to AlertBanner entities.
 *
 * The event names here are the original six, when only localgov_alert_banners
 * were supported. See SchedulerMediaEvents for the generic naming convention
 * to follow for any new entity plugin implementations.
 */
class SchedulerAlertBannerEvents {

  /**
   * The event triggered after a localgov_alert_banner is published immediately.
   *
   * This event allows modules to react after a localgov_alert_banner is
   * published immediately. The event listener method receives a
   * \Drupal\Core\Entity\EntityInterface instance.
   *
   * @Event
   *
   * @see \Drupal\scheduler\Event\SchedulerEvent
   *
   * @var string
   */
  const PUBLISH_IMMEDIATELY = 'scheduler.localgov_alert_banner_publish_immediately';

  /**
   * The event triggered after a localgov_alert_banner is published via cron.
   *
   * This event allows modules to react after a localgov_alert_banner is
   * published. The event listener method receives a
   * \Drupal\Core\Entity\EntityInterface instance.
   *
   * @Event
   *
   * @see \Drupal\scheduler\Event\SchedulerEvent
   *
   * @var string
   */
  const PUBLISH = 'scheduler.localgov_alert_banner_publish';

  /**
   * The event triggered before a localgov_alert_banner is published immediately.
   *
   * This event allows modules to react before a localgov_alert_banner is
   * published immediately. The event listener method receives a
   * \Drupal\Core\Entity\EntityInterface instance.
   *
   * @Event
   *
   * @see \Drupal\scheduler\Event\SchedulerEvent
   *
   * @var string
   */
  const PRE_PUBLISH_IMMEDIATELY = 'scheduler.localgov_alert_banner_pre_publish_immediately';

  /**
   * The event triggered before a localgov_alert_banner is published via cron.
   *
   * This event allows modules to react before a localgov_alert_banner is
   * published. The event listener method receives a
   * \Drupal\Core\Entity\EntityInterface instance.
   *
   * @Event
   *
   * @see \Drupal\scheduler\Event\SchedulerEvent
   *
   * @var string
   */
  const PRE_PUBLISH = 'scheduler.localgov_alert_banner_pre_publish';

  /**
   * The event triggered before a localgov_alert_banner is unpublished via cron.
   *
   * This event allows modules to react before a localgov_alert_banner is
   * unpublished. The event listener method receives a
   * \Drupal\Core\Entity\EntityInterface instance.
   *
   * @Event
   *
   * @see \Drupal\scheduler\Event\SchedulerEvent
   *
   * @var string
   */
  const PRE_UNPUBLISH = 'scheduler.localgov_alert_banner_pre_unpublish';

  /**
   * The event triggered after a localgov_alert_banner is unpublished via cron.
   *
   * This event allows modules to react after a localgov_alert_banner is
   * unpublished. The event listener method receives a
   * \Drupal\Core\Entity\EntityInterface instance.
   *
   * @Event
   *
   * @see \Drupal\scheduler\Event\SchedulerEvent
   *
   * @var string
   */
  const UNPUBLISH = 'scheduler.localgov_alert_banner_unpublish';

}
