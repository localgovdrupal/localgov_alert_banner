<?php

namespace Drupal\localgov_alert_banner\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\DependencyInjection\DeprecatedServicePropertyTrait;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\localgov_alert_banner\AlertBannerManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Alert banner block.
 *
 * @Block(
 *   id = "localgov_alert_banner_block",
 *   admin_label = @Translation("Alert banner"),
 *   category = @Translation("Localgov Alert banner"),
 * )
 */
class AlertBannerBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use DeprecatedServicePropertyTrait;

  /**
   * Defines deprecated injected properties.
   *
   * @var array
   */
  protected array $deprecatedProperties = [
    'currentUser' => 'current_user',
    'entityRepository' => 'entity.repository',
  ];

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Localgov alert banner manager service.
   *
   * @var \Drupal\localgov_alert_banner\AlertBannerManagerInterface
   */
  protected $alertBannerManager;

  /**
   * Constructs a new AlertBannerBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param Drupal\localgov_alert_banner\AlertBannerManagerInterface|Drupal\Core\Session\AccountProxyInterfac $alert_banner_manager
   *   The localgov alert banner manager service.
   * @param Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   *
   * @see https://github.com/localgovdrupal/localgov_alert_banner/wiki/Change-to-alert-banner-block-signature
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AlertBannerManagerInterface|AccountProxyInterface $alert_banner_manager, ?EntityRepositoryInterface $entity_repository = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->alertBannerManager = $alert_banner_manager;
    if ($alert_banner_manager instanceof AccountProxyInterface) {
      // @codingStandardsIgnoreStart
      @trigger_error('Calling ' . __CLASS__ . '::_construct() without the $alert_banner_manager argument is deprecated in localgov_alert_banner:1.8.0 and and it will be required in localgov_alert_banner:2.0.0. See https://github.com/localgovdrupal/localgov_alert_banner/wiki/Change-to-alert-banner-block-signature/', E_USER_DEPRECATED);
      @trigger_error('Calling ' . __CLASS__ . '::_construct() with the $current_user argument is deprecated in localgov_alert_banner:1.8.0 and is removed from localgov_alert_banner:2.0.0. See https://github.com/localgovdrupal/localgov_alert_banner/wiki/Change-to-alert-banner-block-signature', E_USER_DEPRECATED);
      $this->alertBannerManager = \Drupal::service('localgov_alert_banner.manager');
      // @codingStandardsIgnoreEnd
    }
    if ($entity_repository instanceof EntityRepositoryInterface) {
      // @codingStandardsIgnoreLine
      @trigger_error('Calling ' . __CLASS__ . '::_construct() with the $entity_repository argument is deprecated in localgov_alert_banner:1.8.0 and is removed from localgov_alert_banner:2.0.0. See https://github.com/localgovdrupal/localgov_alert_banner/wiki/Change-to-alert-banner-block-signature', E_USER_DEPRECATED);
    }
  }

  /**
   * Create the Alert banner block instance.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container object.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('localgov_alert_banner.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'include_types' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form = parent::blockForm($form, $form_state);
    $type_storage = $this->entityTypeManager->getStorage('localgov_alert_banner_type');
    $config = $this->getConfiguration();
    $config_options = [];
    foreach ($type_storage->loadMultiple() as $id => $type) {
      $config_options[$id] = $type->label();
    }
    $form['include_types'] = [
      '#type' => 'checkboxes',
      '#options' => $config_options,
      '#title' => $this->t('Display types'),
      '#description' => $this->t('If no types are selected all will be displayed.'),
      '#default_value' => !empty($config['include_types']) ? $config['include_types'] : [],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['include_types'] = $values['include_types'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $options = [
      'type' => $this->mapTypesConfigToQuery(),
      'check_visible' => TRUE,
    ];

    // Fetch the current published banner.
    $published_alert_banners = $this->alertBannerManager->getCurrentAlertBanners($options);

    // If no banner found, return NULL so block is not rendered.
    if (empty($published_alert_banners)) {
      return NULL;
    }

    // Render the alert banner.
    $build = [];
    foreach ($published_alert_banners as $alert_banner) {
      $build[] = $this->entityTypeManager->getViewBuilder('localgov_alert_banner')
        ->view($alert_banner);
    }
    return $build;
  }

  /**
   * Get an array of types from config we can use for querying.
   *
   * @return array
   *   An array of alter banner type IDs as keys and values.
   */
  protected function mapTypesConfigToQuery(): array {
    $include_types = $this->configuration['include_types'];
    return array_filter($include_types, function ($t) {
      return (bool) $t;
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = [];
    $options = [
      'type' => $this->mapTypesConfigToQuery(),
      'check_visible' => FALSE,
    ];
    foreach ($this->alertBannerManager->getCurrentAlertBanners($options) as $alert_banner) {
      $contexts = Cache::mergeContexts($contexts, $alert_banner->getCacheContexts());
    }
    return Cache::mergeContexts(parent::getCacheContexts(), $contexts);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Invalidate cache on changes to alert banners.
    return Cache::mergeTags(parent::getCacheTags(), ['localgov_alert_banner_list']);
  }

}
