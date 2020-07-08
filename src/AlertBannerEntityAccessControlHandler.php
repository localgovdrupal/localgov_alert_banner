<?php

namespace Drupal\localgov_alert_banner;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Alert banner entity.
 *
 * @see \Drupal\localgov_alert_banner\Entity\AlertBannerEntity.
 */
class AlertBannerEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface $entity */

    $entity_bundle = $entity->bundle();

    switch ($operation) {

      case 'view':

        if ($account->hasPermission('view all localgov alert banner entities')) {
          return AccessResult::allowed();
        }

        return AccessResult::allowedIfHasPermission($account, 'view localgov alert banner ' . $entity_bundle . ' entities');

      case 'update':

        if ($account->hasPermission('manage all localgov alert banner entities')) {
          return AccessResult::allowed();
        }
        return AccessResult::allowedIfHasPermission($account, 'manage localgov alert banner ' . $entity_bundle . ' entities');

      case 'delete':

        if ($account->hasPermission('manage all localgov alert banner entities')) {
          return AccessResult::allowed();
        }
        return AccessResult::allowedIfHasPermission($account, 'manage localgov alert banner ' . $entity_bundle . ' entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    if ($account->hasPermission('manage all localgov alert banner entities')) {
      return AccessResult::allowed();
    }
    return AccessResult::allowedIfHasPermission($account, 'manage localgov alert banner ' . $entity_bundle . ' entities');
  }

}
