<?php

namespace Drupal\localgov_alert_banner;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface;

/**
 * Defines the storage handler class for Alert banner entities.
 *
 * This extends the base storage class, adding required special handling for
 * Alert banner entities.
 *
 * @ingroup localgov_alert_banner
 */
interface AlertBannerEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Alert banner revision IDs for a specific Alert banner.
   *
   * @param \Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface $entity
   *   The Alert banner entity.
   *
   * @return int[]
   *   Alert banner revision IDs (in ascending order).
   */
  public function revisionIds(AlertBannerEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Alert banner author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Alert banner revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface $entity
   *   The Alert banner entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(AlertBannerEntityInterface $entity);

  /**
   * Unsets the language for all Alert banner with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
