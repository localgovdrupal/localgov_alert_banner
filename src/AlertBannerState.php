<?php

namespace Drupal\localgov_alert_banner;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\State\StateInterface;

/**
 * Class AlertBannerState.
 *
 * @package Drupal\localgov_alert_banner
 * @ingroup localgov_alert_banner
 */
class AlertBannerState {

  /**
   * State object.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Alert banner hide token.
   *
   * @var string
   */
  protected $token;

  /**
   * AlertBannerState constructor.
   *
   * @param Drupal\Core\State\StateInterface $state
   *   State service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
    $this->token = $state->get('localgov_alert_banner.alert_banner_token');
  }

  /**
   * Generate token.
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $entity
   *   Alert banner entity.
   *
   * @return $this
   *   Self (class can be be method chained).
   */
  public function generateToken(ContentEntityBase $entity) {
    $prefix = 'alert-' . $entity->id();
    $hash = sha1(uniqid('', TRUE));
    $this->token = $prefix . '-' . $hash;

    return $this;
  }

  /**
   * Get token.
   *
   * @return mixed
   *   The token, or NULL if not set.
   */
  public function getToken() {
    return $this->token ?? NULL;
  }

  /**
   * Save token.
   *
   * @return mixed
   *   Result from saving to state.
   */
  public function save() {
    return $this->state->set('localgov_alert_banner.alert_banner_token', $this->token);
  }

}
