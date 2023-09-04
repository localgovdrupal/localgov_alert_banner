<?php

namespace Drupal\localgov_alert_banner;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\BundlePermissionHandlerTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions for Alert banner of different types.
 *
 * @ingroup localgov_alert_banner
 */
class AlertBannerEntityPermissions implements ContainerInjectionInterface {

  use BundlePermissionHandlerTrait;
  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Alert banner permissions constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Returns an array of alert banner type permissions.
   *
   * @return array
   *   The alert banner type permissions.
   *
   * @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  public function alertBannerTypePermissions() {
    // Generate media permissions for all media types.
    $alert_banner_types = $this->entityTypeManager
      ->getStorage('localgov_alert_banner_type')
      ->loadMultiple();
    return $this->generatePermissions($alert_banner_types, [
      $this,
      'buildPermissions',
    ]);
  }

  /**
   * Returns a list of node permissions for a given node type.
   *
   * @param \Drupal\localgov_alert_banner\Entity\AlertBannerEntityType $type
   *   The AlertBannerEntity type.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function buildPermissions(AlertBannerEntityType $type) {
    $type_id = $type->id();
    $type_params = ['%type_name' => $type->label()];

    return [
      // View alert banner entities of type.
      "view localgov alert banner $type_id entities" => [
        'title' => $this->t('View alert banner entities of type %type_name', $type_params),
      ],

      // View alert banner entities of type.
      "view localgov alert banner $type_id pages" => [
        'title' => $this->t('View alert banner pages of type %type_name', $type_params),
      ],

      // Manage the alert banner of type (full CRUD permissions).
      "manage localgov alert banner $type_id entities" => [
        'title' => $this->t('Manage alert banner entities of type %type_name', $type_params),
      ],
    ];
  }

}
