<?php

namespace Drupal\localgov_alert_banner\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlertBannerEntityController.
 *
 *  Returns responses for Alert banner routes.
 */
class AlertBannerEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Publish or Unpublish title for alert banner status change form.
   */
  public function getStatusFormTitle(AlertBannerEntityInterface $localgov_alert_banner) {
    return $localgov_alert_banner->isPublished() ? $this->t('Remove banner') : $this->t('Put banner live');
  }

}
