<?php

namespace Drupal\localgov_alert_banner;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Alert banner entity.
 *
 * @see \Drupal\localgov_alert_banner\Entity\AlertBanner.
 */
class AlertBannerAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\localgov_alert_banner\Entity\AlertBannerInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished alert banner entities');
        }

        return AccessResult::allowedIfHasPermission($account, 'view published alert banner entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit alert banner entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete alert banner entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add alert banner entities');
  }

}
