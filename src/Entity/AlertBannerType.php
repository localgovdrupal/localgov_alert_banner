<?php

namespace Drupal\localgov_alert_banner\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Alert banner type entity.
 *
 * @ConfigEntityType(
 *   id = "localgov_alert_banner_type",
 *   label = @Translation("Alert Banner type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\localgov_alert_banner\AlertBannerTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\localgov_alert_banner\Form\AlertBannerTypeForm",
 *       "edit" = "Drupal\localgov_alert_banner\Form\AlertBannerTypeForm",
 *       "delete" = "Drupal\localgov_alert_banner\Form\AlertBannerTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\localgov_alert_banner\AlertBannerTypeHtmlRouteProvider",
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
 *     "canonical" = "/admin/structure/localgov_alert_banner_type/{localgov_alert_banner_type}",
 *     "add-form" = "/admin/structure/localgov_alert_banner_type/add",
 *     "edit-form" = "/admin/structure/localgov_alert_banner_type/{localgov_alert_banner_type}/edit",
 *     "delete-form" = "/admin/structure/localgov_alert_banner_type/{localgov_alert_banner_type}/delete",
 *     "collection" = "/admin/structure/localgov_alert_banner_type"
 *   }
 * )
 */
class AlertBannerType extends ConfigEntityBundleBase implements AlertBannerTypeInterface {

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
