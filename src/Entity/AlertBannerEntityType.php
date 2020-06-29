<?php

namespace Drupal\localgov_alert_banner\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Alert banner type entity.
 *
 * @ConfigEntityType(
 *   id = "localgov_alert_banner_type",
 *   label = @Translation("Alert banner type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\localgov_alert_banner\AlertBannerEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\localgov_alert_banner\Form\AlertBannerEntityTypeForm",
 *       "edit" = "Drupal\localgov_alert_banner\Form\AlertBannerEntityTypeForm",
 *       "delete" = "Drupal\localgov_alert_banner\Form\AlertBannerEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\localgov_alert_banner\AlertBannerEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "localgov_alert_banner_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "localgov_alert_banner",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/alert-banner-types/localgov_alert_banner_type/{localgov_alert_banner_type}",
 *     "add-form" = "/admin/structure/alert-banner-types/localgov_alert_banner_type/add",
 *     "edit-form" = "/admin/structure/alert-banner-types/localgov_alert_banner_type/{localgov_alert_banner_type}/edit",
 *     "delete-form" = "/admin/structure/alert-banner-types/localgov_alert_banner_type/{localgov_alert_banner_type}/delete",
 *     "collection" = "/admin/structure/alert-banner-types/localgov_alert_banner_type"
 *   }
 * )
 */
class AlertBannerEntityType extends ConfigEntityBundleBase implements AlertBannerEntityTypeInterface {

  /**
   * The Alert banner type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Alert banner type label.
   *
   * @var string
   */
  protected $label;

}
