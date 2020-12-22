<?php

namespace Drupal\Tests\localgov_alert_banner\Kernel;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\KernelTests\KernelTestBase;

/**
 * Kernel test for alert banner order.
 *
 * @group localgov_alert_banner
 */
class AlertBannerBlockOrderTest extends KernelTestBase {

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
   * Test alert banner block order.
   */
  public function testAlertBannerBlockOrder() {

    // Alert details to set.
    $alert_details = [
      '00--announcement' => 'Announcement',
      '20--minor' => 'Minor alert',
      '50--major' => 'Emergency',
      '90--notable-person' => 'Death of a notable person',
    ];

    $count = 1;
    foreach ($alert_details as $key => $value) {
      $alert_entity = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
        ->create([
          'type' => 'localgov_alert_banner',
          'title' => $count,
          'type_of_alert' => $key,
          'status' => TRUE,
          // Make sure post in past for further test.
          'changed' => (new DrupalDateTime('-2 hours'))->getTimestamp(),
        ]);
      $alert_entity->save();
      $alert[] = $alert_entity;
      $count++;
    }

    // Create a block instance of gthe alert banner block.
    $block_manager = $this->container->get('plugin.manager.block');
    $config = [];
    $plugin_block = $block_manager->createInstance('localgov_alert_banner_block', $config);

    $render = $plugin_block->build();
    foreach ($render as $render_value) {
      $result[] = $render_value['#localgov_alert_banner']->label();
    }

    // Order should be RIP, Major, Minor, Annoucment.
    $expected = ['4', '3', '2', '1'];

    $this->assertEquals($expected, $result);

    // More banners - Order should be type then most recent first.
    $count = 5;
    foreach ($alert_details as $key => $value) {
      $alert_entity = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
        ->create([
          'type' => 'localgov_alert_banner',
          'title' => $count,
          'type_of_alert' => $key,
          'status' => TRUE,
          'changed' => (new DrupalDateTime('-1 hour'))->getTimestamp(),
        ]);
      $alert_entity->save();
      $alert[] = $alert_entity;
      $count++;
    }

    $render = $plugin_block->build();
    foreach ($render as $render_value) {
      $result_2[] = $render_value['#localgov_alert_banner']->label();
    }

    // Order should be RIP, Major, Minor, Annoucment - most recent first.
    $expected_2 = ['8', '4', '7', '3', '6', '2', '5', '1'];

    $this->assertEquals($expected_2, $result_2);

    // Update the changed date of the first 4 banners to now.
    for ($i = 0; $i <= 3; $i++) {
      $alert[$i]->set('changed', (new DrupalDateTime('now'))->getTimestamp())->save();
    }

    $render = $plugin_block->build();
    foreach ($render as $render_value) {
      $result_3[] = $render_value['#localgov_alert_banner']->label();
    }

    // Order will be flipped around.
    $expected_3 = ['4', '8', '3', '7', '2', '6', '1', '5'];

    $this->assertEquals($expected_3, $result_3);

  }

}
