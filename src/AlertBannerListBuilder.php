<?php

namespace Drupal\localgov_alert_banner;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Alert banner entities.
 *
 * @ingroup localgov_alert_banner
 */
class AlertBannerListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Alert banner ID');
    $header['title'] = $this->t('Title');
    $header['type'] = $this->t('Type');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\localgov_alert_banner\Entity\AlertBanner $entity */
    $row['id'] = $entity->id();
    $row['title'] = Link::createFromRoute(
      $entity->label(),
      'entity.localgov_alert_banner.edit_form',
      ['localgov_alert_banner' => $entity->id()]
    );
    $row['type'] = $entity->getType();
    return $row + parent::buildRow($entity);
  }

}
