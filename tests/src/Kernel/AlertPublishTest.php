<?php

namespace Drupal\Tests\localgov_alert_banner\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntity;

/**
 * Kernel test publish / unpublish alert banners.
 *
 * @group localgov_alert_banner
 */
class AlertPublishTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'field',
    'text',
    'options',
    'link',
    'user',
    'block',
    'views',
    'localgov_alert_banner',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setup();

    $this->installEntitySchema('user');
    $this->installEntitySchema('localgov_alert_banner');
    $this->installConfig([
      'system',
      'localgov_alert_banner',
    ]);
  }

  /**
   * Test publishing and unpublishing alert banners.
   */
  public function testPublishAlertBanner() {
    $title = $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert[0] = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title,
        'short_description' => $alert_message,
        'type_of_alert' => 'minor',
        'status' => TRUE,
      ]);
    $alert[0]->save();
    $this->assertTrue(AlertBannerEntity::load($alert[0]->id())->isPublished());

    $title = $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert[1] = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title,
        'short_description' => $alert_message,
        'type_of_alert' => 'minor',
        'status' => FALSE,
      ]);
    $alert[1]->save();

    $this->assertTrue(AlertBannerEntity::load($alert[0]->id())->isPublished(), 'Original banner remains published');
    $this->assertFalse(AlertBannerEntity::load($alert[1]->id())->isPublished(), 'New banner unpublished');

    $alert[1]->status = TRUE;
    $alert[1]->save();

    $this->assertFalse(AlertBannerEntity::load($alert[0]->id())->isPublished(), 'Original banner unpublished');
    $this->assertTrue(AlertBannerEntity::load($alert[1]->id())->isPublished(), 'New banner published');
  }

}
