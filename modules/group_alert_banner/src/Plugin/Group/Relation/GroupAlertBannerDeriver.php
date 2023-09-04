<?php

namespace Drupal\group_alert_banner\Plugin\Group\Relation;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\group\Plugin\Group\Relation\GroupRelationTypeInterface;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntityType;

/**
 * Alert banner plugin deriver for Group.
 */
class GroupAlertBannerDeriver extends DeriverBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    assert($base_plugin_definition instanceof GroupRelationTypeInterface);
    $this->derivatives = [];

    foreach (AlertBannerEntityType::loadMultiple() as $name => $alert_banner_type) {
      $label = $alert_banner_type->label();

      $this->derivatives[$name] = clone $base_plugin_definition;
      $this->derivatives[$name]->set('entity_bundle', $name);
      $this->derivatives[$name]->set('label', $this->t('Group Alert banner (@type)', ['@type' => $label]));
      $this->derivatives[$name]->set('description', $this->t('Adds %type Alert banner to groups both publicly and privately.', ['%type' => $label]));
    }

    return $this->derivatives;
  }

}
