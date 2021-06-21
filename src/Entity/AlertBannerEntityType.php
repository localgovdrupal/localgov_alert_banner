<?php

namespace Drupal\localgov_alert_banner\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

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
 *   admin_permission = "administer localgov alert banner types",
 *   bundle_of = "localgov_alert_banner",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label"
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

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {

    // Add fields from install config when creating a new alert banner type.
    if (!$update) {

      $bundle = $this->id();
      $config_directory = new FileStorage(__DIR__ . '/../../config/install');

      // Fields to add to the new alert banner type.
      $fields_to_add = [
        'visibility',
      ];

      foreach ($fields_to_add as $field_name) {

        // Add field storage if necessary (it may have been deleted).
        $field_storage = $config_directory->read('field.storage.localgov_alert_banner.' . $field_name);
        if ($field_storage && !FieldStorageConfig::loadByName('localgov_alert_banner', $field_name)) {
          FieldStorageConfig::create($field_storage)->save();
        }

        // Add field config for new bundle.
        $field_record = $config_directory->read('field.field.localgov_alert_banner.localgov_alert_banner.' . $field_name);
        if ($field_record && !FieldConfig::loadByName('localgov_alert_banner', $bundle, $field_name)) {
          $field_record['bundle'] = $bundle;
          FieldConfig::create($field_record)->save();
        }
      }
    }
  }

}
