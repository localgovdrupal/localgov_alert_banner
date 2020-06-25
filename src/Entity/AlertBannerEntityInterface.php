<?php

namespace Drupal\localgov_alert_banner\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Alert banner entities.
 *
 * @ingroup localgov_alert_banner
 */
interface AlertBannerEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Alert banner name.
   *
   * @return string
   *   Name of the Alert banner.
   */
  public function getTitle();

  /**
   * Sets the Alert banner name.
   *
   * @param string $title
   *   The Alert banner name.
   *
   * @return \Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface
   *   The called Alert banner entity.
   */
  public function setTitle($title);

  /**
   * Gets the Alert banner creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Alert banner.
   */
  public function getCreatedTime();

  /**
   * Sets the Alert banner creation timestamp.
   *
   * @param int $timestamp
   *   The Alert banner creation timestamp.
   *
   * @return \Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface
   *   The called Alert banner entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Alert banner revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Alert banner revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface
   *   The called Alert banner entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Alert banner revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Alert banner revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface
   *   The called Alert banner entity.
   */
  public function setRevisionUserId($uid);

}
