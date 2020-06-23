<?php

/**
* @file
* Contains \Drupal\localgov_alert_banner\EventSubscriber\AlertBannerFlagLive.
* @src
* https://dev.studiopresent.com/blog/back-end/perform-actions-flag-unflag-drupal-8
*/

namespace Drupal\localgov_alert_banner\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\flag\Event\FlagEvents;
use Drupal\flag\Event\FlaggingEvent;

class AlertBannerFlagLive implements EventSubscriberInterface {

  /**
   * on Flag event
   * @param  \Drupal\flag\Event\FlaggingEvent $event
   */
  public function onFlag(FlaggingEvent $event) {

    $flagging = $event->getFlagging();
    $entityId = $flagging->getFlaggable()->id();
    $flagType = $flagging->getFlagId();

    // Make sure we only act on the put live flag
    if ($flagType == 'set_live') {

      $flag = \Drupal::service('flag')->getFlagById($flagType);

      // Get existing flagging entity ids
      $existingFlagIds = $this->getExistingFlagIds($flagging->id(), $flagType);

      // Send them to be unflagged
      $this->unflagExistingFlags($flag, $existingFlagIds);

      // Regenerate JS token
      \Drupal::service('localgov_alert_banner.state')->generateToken($flagging->getFlaggable())->save();

    }
  }

  /**
   * Get existing flag IDs
   * @param  int    $id       Current Flagging Entity ID (to exclude)
   * @param  string $flagType flag_id
   * @return array            Array of existing flag IDs, excluding the current
   */
  private function getExistingFlagIds(int $id, string $flagType) {

    $flagQuery = \Drupal::entityTypeManager()->getStorage('flagging')->getQuery();
    $existingFlagIds = $flagQuery->condition('flag_id', $flagType)
                                 ->condition('id', $id, '!=')
                                 ->execute();
    return $existingFlagIds;
  }

  /**
   * Unflag existing flags
   *
   * Use instead of flags own unflagAllByEntity so to exclude the current banner
   * @param  \Drupal\flag\FlagInterface $flag
   * @param  array                      $existingFlagIds flag IDs to unflag
   */
  private function unflagExistingFlags(\Drupal\flag\FlagInterface $flag, array $existingFlagIds) {

    $existingFlags = \Drupal::entityTypeManager()->getStorage('flagging')->loadMultiple($existingFlagIds);

    // Unflag any live alert banner
    // Ideally, this should only be a previously flagged alert banner
    foreach($existingFlags as $existingFlagEntity) {
      $existingFlaggedBanner = $existingFlagEntity->getFlaggable();
      \Drupal::service('flag')->unflag($flag, $existingFlaggedBanner);
    }
  }


  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[FlagEvents::ENTITY_FLAGGED][] = ['onFlag'];
    return $events;
  }

}
