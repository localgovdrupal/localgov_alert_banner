<?php

namespace Drupal\Tests\localgov_alert_banner\Kernel;

use Drupal\Core\Extension\MissingDependencyException;
use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\Role;

/**
 * Test enabling scheduling.
 *
 * @group localgov_alert_banner
 */
class SchedulingInstallTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'condition_field',
    'content_moderation',
    'field',
    'link',
    'options',
    'system',
    'text',
    'user',
    'views',
    'workflows',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('workflow');
    $this->installEntitySchema('user');
    $this->installConfig([
      'content_moderation',
      'system',
    ]);

  }

  /**
   * Check alert banners are configured when enabling scheduled transitions.
   */
  public function testEnableScheduledTransitions() {
    \Drupal::service('module_installer')->install(['localgov_alert_banner']);

    // Add extra alert banner type.
    $this->container->get('entity_type.manager')
      ->getStorage('localgov_alert_banner_type')
      ->create(['id' => 'extra_type', 'label' => 'Extra type'])
      ->save();

    // Check scheduled transitions config.
    $bundles = \Drupal::service('config.factory')->get('scheduled_transitions.settings')->get('bundles');
    $this->assertEmpty($bundles);
    try {
      \Drupal::service('module_installer')->install(['scheduled_transitions']);
    }
    catch (MissingDependencyException $e) {
      return;
    }
    $bundles = \Drupal::service('config.factory')->get('scheduled_transitions.settings')->get('bundles');
    $this->assertEquals([
      [
        'entity_type' => 'localgov_alert_banner',
        'bundle' => 'extra_type',
      ],
      [
        'entity_type' => 'localgov_alert_banner',
        'bundle' => 'localgov_alert_banner',
      ],
    ], $bundles);

    // Check permissions.
    $publisher = Role::load('emergency_publisher');
    $permissions = [
      'add scheduled transitions localgov_alert_banner localgov_alert_banner',
      'add scheduled transitions localgov_alert_banner extra_type',
      'reschedule scheduled transitions localgov_alert_banner localgov_alert_banner',
      'reschedule scheduled transitions localgov_alert_banner extra_type',
      'view scheduled transitions localgov_alert_banner localgov_alert_banner',
      'view scheduled transitions localgov_alert_banner extra_type',
    ];
    foreach ($permissions as $permission) {
      $this->assertTrue($publisher->hasPermission($permission));
    }
  }

  /**
   * Check scheduled transitions are configured when enabling alert banners.
   */
  public function testEnableLocalGovAlertBanner() {
    try {
      \Drupal::service('module_installer')->install(['scheduled_transitions']);
    }
    catch (MissingDependencyException $e) {
      return;
    }

    // Check scheduled transitions config.
    $bundles = \Drupal::service('config.factory')->get('scheduled_transitions.settings')->get('bundles');
    $this->assertEmpty($bundles);
    \Drupal::service('module_installer')->install(['localgov_alert_banner']);
    $bundles = \Drupal::service('config.factory')->get('scheduled_transitions.settings')->get('bundles');
    $this->assertEquals([
      [
        'entity_type' => 'localgov_alert_banner',
        'bundle' => 'localgov_alert_banner',
      ],
    ], $bundles);

    // Check permissions.
    $publisher = Role::load('emergency_publisher');
    $permissions = [
      'add scheduled transitions localgov_alert_banner localgov_alert_banner',
      'reschedule scheduled transitions localgov_alert_banner localgov_alert_banner',
      'view scheduled transitions localgov_alert_banner localgov_alert_banner',
    ];
    foreach ($permissions as $permission) {
      $this->assertTrue($publisher->hasPermission($permission));
    }
  }

}
