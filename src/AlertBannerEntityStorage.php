<?php

namespace Drupal\localgov_alert_banner;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class AlertBannerEntityStorage extends SqlContentEntityStorage implements AlertBannerEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(AlertBannerEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {localgov_alert_banner_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {localgov_alert_banner_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(AlertBannerEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {localgov_alert_banner_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('localgov_alert_banner_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
