<?php

declare(strict_types=1);

namespace Drupal\localgov_alert_banner;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Localgov alert banner manager service.
 *
 * @ingroup localgov_alert_banner
 */
class AlertBannerManager implements AlertBannerManagerInterface {

  /**
   * Constructs a localgov alert banner manager service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   Current user service.
   * @param Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly AccountProxyInterface $account,
    private readonly EntityRepositoryInterface $entityRepository,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getCurrentAlertBanners(array $options): array {

    // Set default options.
    $default_options = [
      'type' => [],
      'check_visible' => FALSE,
    ];
    $options = array_merge($default_options, $options);

    $current_alert_banners = [];

    $alert_banner_storage = $this->entityTypeManager->getStorage('localgov_alert_banner');

    // Get list of published alert banner IDs.
    $published_alert_banner_query = $alert_banner_storage->getQuery()
      ->condition('status', 1);

    // Only order by type of alert if the field is present.
    $alert_banner_has_type_of_alert = FieldStorageConfig::loadByName('localgov_alert_banner', 'type_of_alert');
    if (!empty($alert_banner_has_type_of_alert)) {
      $published_alert_banner_query->sort('type_of_alert', 'DESC');
    }

    // Continue alert banner query.
    $published_alert_banner_query->sort('changed', 'DESC');

    // If types (bunldes) are selected, add filter condition.
    if (!empty($options['type'])) {
      $published_alert_banner_query->condition('type', $options['type'], 'IN');
    }

    // Execute alert banner query.
    $published_alert_banners = $published_alert_banner_query
      ->accessCheck(TRUE)
      ->execute();

    // Load alert banners and add all.
    // Visibility check happens separately, so we get cache contexts on all.
    if (!empty($published_alert_banners)) {
      foreach ($alert_banner_storage->loadMultiple($published_alert_banners) as $alert_banner) {
        $alert_banner = $this->entityRepository->getTranslationFromContext($alert_banner);

        $is_accessible = $alert_banner->access('view', $this->account);
        if ($is_accessible) {
          $current_alert_banners[] = $alert_banner;
        }
      }
    }

    // Check visibility if specified.
    // Should only be when banners are being displayed.
    // @see #154.
    if ($options['check_visible']) {
      $current_alert_banners = array_filter($current_alert_banners, function ($alert_banner) {
        return $alert_banner->isVisible();
      });
    }

    return $current_alert_banners;
  }

}
