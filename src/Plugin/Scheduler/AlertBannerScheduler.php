<?php

namespace Drupal\localgov_alert_banner\Plugin\Scheduler;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\scheduler\SchedulerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin for Localgov Alert Banner entity type.
 *
 * @package Drupal\Scheduler\Plugin\Scheduler
 *
 * @SchedulerPlugin(
 *  id = "localgov_alert_banner_scheduler",
 *  label = @Translation("Alert Banner Scheduler Plugin"),
 *  description = @Translation("Provides support for scheduling localgov_alert_banner entities"),
 *  entityType = "localgov_alert_banner",
 *  typeFieldName = "type",
 *  dependency = "localgov_alert_banner",
 *  idListFunction = "scheduler_localgov_alert_banner_id",
 *  develGenerateForm = "devel_generate_form_content",
 * )
 */
class AlertBannerScheduler extends SchedulerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Create method.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('string_translation')
    );
  }

  /**
   * Get the available types/bundles for the entity type.
   *
   * Do not use static or drupal_static here, because changes to third-party
   * settings invalidate the saved values during phpunit testing.
   *
   * @return array
   *   The localgov_alert_banner type objects.
   */
  public function getTypes() {
    $localgov_alert_bannerTypes = \Drupal::entityTypeManager()->getStorage('localgov_alert_banner_type')->loadMultiple();
    return $localgov_alert_bannerTypes;
  }

  /**
   * Get the form IDs for localgov_alert_banner add/edit forms.
   *
   * @return array
   *   The list of form IDs.
   */
  public function entityFormIds() {
    static $ids;
    if (!isset($ids)) {
      $ids = [];
      $types = array_keys($this->getTypes());
      foreach ($types as $typeId) {
        // The localgov_alert_banner add form is named localgov_alert_banner_{type}_form. This is different from
        // other entities, which have {entity}_{type}_add_form.
        $ids[] = 'localgov_alert_banner_' . $typeId . '_add_form';
        $ids[] = 'localgov_alert_banner_' . $typeId . '_edit_form';
      }
    }
    return $ids;
  }

  /**
   * Get the form IDs for localgov_alert_banner type forms.
   *
   * @return array
   *   The list of form IDs.
   */
  public function entityTypeFormIds() {
    return [
      'localgov_alert_banner_type_form',
      'localgov_alert_banner_type_edit_form',
    ];
  }

}
