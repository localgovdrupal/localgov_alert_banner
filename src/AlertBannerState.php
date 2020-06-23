<?php

namespace Drupal\localgov_alert_banner;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\State\State;

/**
 * Class AlertBannerState.
 *
 * @package Drupal\localgov_alert_banner
 */
class AlertBannerState {

  /**
   * @var \Drupal\Core\State\State
   */
  protected $state;

  /**
   * @var array
   */
  protected $token;

  /**
   * AlertBannerState constructor.
   *
   * @param Drupal\Core\State\State $state
   */
  public function __construct(State $state) {
    $this->state = $state;
    $this->token = $state->get('localgov_alert_banner.alert_banner_token');
  }

  /**
   * @param  \Drupal\Core\Entity\ContentEntityBase $entity
   * @return $this
   */
  public function generateToken(ContentEntityBase $entity) {
    $prefix = 'alert-' . $entity->id();
    $hash = sha1(uniqid('', TRUE));
    $this->token = $prefix . '-' . $hash;

    return $this;
  }

  /**
   * @return string
   */
  public function getToken() {
    return $this->token ?? NULL;
  }

  /**
   * @return mixed
   */
  public function save() {
    return $this->state->set('localgov_alert_banner.alert_banner_token', $this->token);
  }

}
