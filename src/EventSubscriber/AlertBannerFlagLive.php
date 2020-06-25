<?php

namespace Drupal\localgov_alert_banner\EventSubscriber;

use Drupal\flag\FlagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\flag\FlagServiceInterface;
use Drupal\flag\Event\FlagEvents;
use Drupal\flag\Event\FlaggingEvent;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\localgov_alert_banner\AlertBannerState;

/**
 * Provides an event subscriver for the flag event.
 *
 * @package Drupal\localgov_alert_banner\EventSubscriber
 * @ingroup localgov_alert_banner
 */
class AlertBannerFlagLive implements EventSubscriberInterface {

  /**
   * Flag service object.
   *
   * @var \Drupal\flag\FlagServiceInterface
   */
  protected $flagService;

  /**
   * Entity type manager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Alert Banner State object.
   *
   * @var \Drupal\localgov_alert_banner\AlertBannerState
   */
  protected $alertBannerState;

  /**
   * Class contructor.
   *
   * @param \Drupal\flag\FlagServiceInterface $flag_service
   *   Flag service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\localgov_alert_banner\AlertBannerState $alert_banner_state
   *   Localgov Alert banner state.
   */
  public function __construct(FlagServiceInterface $flag_service, EntityTypeManagerInterface $entity_type_manager, AlertBannerState $alert_banner_state) {
    $this->flagService = $flag_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->alertBannerState = $alert_banner_state;
  }

  /**
   * On Flag event.
   *
   * @param \Drupal\flag\Event\FlaggingEvent $event
   *   Flag event.
   */
  public function onFlag(FlaggingEvent $event) {

    $flagging = $event->getFlagging();
    $flagType = $flagging->getFlagId();

    // Make sure we only act on the put live flag.
    if ($flagType == 'localgov_put_live') {

      $flag = $this->flagService->getFlagById($flagType);

      // Get existing flagging entity ids.
      $existingFlagIds = $this->getExistingFlagIds($flagging->id(), $flagType);

      // Send them to be unflagged.
      $this->unflagExistingFlags($flag, $existingFlagIds);

      // Regenerate JS token.
      $this->alertBannerState->generateToken($flagging->getFlaggable())->save();

    }
  }

  /**
   * Get existing flag IDs.
   *
   * @param int $id
   *   Current Flagging Entity ID (to exclude)
   * @param string $flagType
   *   Flag_id.
   *
   * @return array
   *   Array of existing flag IDs, excluding the current entity.
   */
  private function getExistingFlagIds(int $id, string $flagType) {

    $flagQuery = $this->entityTypeManager->getStorage('flagging')->getQuery();
    $existingFlagIds = $flagQuery->condition('flag_id', $flagType)
      ->condition('id', $id, '!=')
      ->execute();
    return $existingFlagIds;
  }

  /**
   * Unflag existing flags.
   *
   * Use instead of flags own unflagAllByEntity
   * so to exclude the current banner.
   *
   * @param \Drupal\flag\FlagInterface $flag
   *   The flag object.
   * @param array $existingFlagIds
   *   Flag IDs to unflag.
   */
  private function unflagExistingFlags(FlagInterface $flag, array $existingFlagIds) {

    $existingFlags = $this->entityTypeManager->getStorage('flagging')->loadMultiple($existingFlagIds);

    // Unflag any live alert banner
    // Ideally, this should only be a previously flagged alert banner.
    foreach ($existingFlags as $existingFlagEntity) {
      $existingFlaggedBanner = $existingFlagEntity->getFlaggable();
      $this->flagService->unflag($flag, $existingFlaggedBanner);
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
