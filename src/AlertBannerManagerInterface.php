<?php

declare(strict_types=1);

namespace Drupal\localgov_alert_banner;

/**
 * Interface for localgov alert banners manager service.
 *
 * @ingroup localgov_alert_banner
 */
interface AlertBannerManagerInterface {

  /**
   * Get current alert banner(s).
   *
   * Note: Default order will be by the field type_of_alert
   * (only on the default) and then updated date.
   *
   * @param array $options
   *   An array of options to filter the query.
   *
   * @return \Drupal\localgov_alert_banner\Entity\AlertBannerEntity[]
   *   Array of all published alert banners regardless of visibility.
   */
  public function getCurrentAlertBanners(array $options): array;

}
