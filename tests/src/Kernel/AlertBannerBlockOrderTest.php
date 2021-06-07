<?php

namespace Drupal\Tests\localgov_alert_banner\Kernel;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\KernelTests\KernelTestBase;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntity;

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
  protected static $modules = [
    'system',
    'field',
    'text',
    'options',
    'link',
    'user',
    'block',
    'views',
    'condition_field',
    'localgov_alert_banner',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
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

    // Set up alert banners.
    foreach ($alert_details as $key => $value) {
      $alert_entity = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
        ->create([
          'type' => 'localgov_alert_banner',
          'title' => $this->randomMachineName(8),
          'type_of_alert' => $key,
          'status' => TRUE,
          // Make sure post in past for further test.
          'changed' => (new DrupalDateTime('-2 hours'))->getTimestamp(),
        ]);
      $alert_entity->save();
      $alert[] = $alert_entity->id();
    }

    // Create a block instance of the alert banner block.
    $block_manager = $this->container->get('plugin.manager.block');
    $config = [];
    $plugin_block = $block_manager->createInstance('localgov_alert_banner_block', $config);

    // Render the block and get the alert banner IDs as an array.
    $render = $plugin_block->build();
    foreach ($render as $render_value) {
      $result[] = $render_value['#localgov_alert_banner']->id();
    }

    // Order should be RIP, Major, Minor, Annoucment.
    // The result should be the input array in reverse order.
    $expected = [$alert[3], $alert[2], $alert[1], $alert[0]];
    $this->assertEquals($expected, $result);

    // More banners - Order should be type then most recent first.
    foreach ($alert_details as $key => $value) {
      $alert_entity = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
        ->create([
          'type' => 'localgov_alert_banner',
          'title' => $this->randomMachineName(8),
          'type_of_alert' => $key,
          'status' => TRUE,
          'changed' => (new DrupalDateTime('-1 hour'))->getTimestamp(),
        ]);
      $alert_entity->save();
      $alert_new[] = $alert_entity->id();
    }

    // Create and render the block and get the alert banner IDs as an array.
    $plugin_block = $block_manager->createInstance('localgov_alert_banner_block', $config);
    $render = $plugin_block->build();
    foreach ($render as $render_value) {
      $result_2[] = $render_value['#localgov_alert_banner']->id();
    }

    // Order should be RIP, Major, Minor, Annoucment - most recent first.
    $expected_2 = [
      $alert_new[3],
      $alert[3],
      $alert_new[2],
      $alert[2],
      $alert_new[1],
      $alert[1],
      $alert_new[0],
      $alert[0],
    ];
    $this->assertEquals($expected_2, $result_2);

    // Update the changed date of the first 4 banners to now.
    for ($i = 0; $i <= 3; $i++) {
      AlertBannerEntity::load($alert[$i])->set('changed', (new DrupalDateTime('now'))->getTimestamp())->save();
    }

    // Create and render the block and get the alert banner IDs as an array.
    $plugin_block = $block_manager->createInstance('localgov_alert_banner_block', $config);
    $render = $plugin_block->build();
    foreach ($render as $render_value) {
      $result_3[] = $render_value['#localgov_alert_banner']->id();
    }

    // Order will be flipped around.
    $expected_3 = [
      $alert[3],
      $alert_new[3],
      $alert[2],
      $alert_new[2],
      $alert[1],
      $alert_new[1],
      $alert[0],
      $alert_new[0],
    ];
    $this->assertEquals($expected_3, $result_3);

  }

}
