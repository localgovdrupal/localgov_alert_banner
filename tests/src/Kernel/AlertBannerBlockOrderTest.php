<?php

namespace Drupal\Tests\localgov_alert_banner\Kernel;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\KernelTests\KernelTestBase;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntity;
use Drupal\user\RoleInterface;

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
    'content_moderation',
    'workflows',
    'localgov_alert_banner',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('content_moderation_state');
    $this->installEntitySchema('user');
    $this->installEntitySchema('workflow');
    $this->installEntitySchema('localgov_alert_banner');
    $this->installConfig([
      'content_moderation',
      'system',
      'localgov_alert_banner',
      'user',
    ]);

    // Default grant permissions to view all alert banners.
    user_role_grant_permissions(RoleInterface::ANONYMOUS_ID, ['view all localgov alert banner entities']);
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
    $alert = [];
    foreach ($alert_details as $key => $value) {
      $alert_entity = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
        ->create([
          'type' => 'localgov_alert_banner',
          'title' => $this->randomMachineName(8),
          'type_of_alert' => $key,
          'moderation_state' => 'published',
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
    $render = $this->getBannersFromBlockRenderArray($plugin_block);
    $result = [];
    foreach ($render as $render_value) {
      $result[] = $render_value['#localgov_alert_banner']->id();
    }

    // Order should be RIP, Major, Minor, Annoucment.
    // The result should be the input array in reverse order.
    $expected = [$alert[3], $alert[2], $alert[1], $alert[0]];
    $this->assertEquals($expected, $result);

    // More banners - Order should be type then most recent first.
    $alert_new = [];
    foreach ($alert_details as $key => $value) {
      $alert_entity = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
        ->create([
          'type' => 'localgov_alert_banner',
          'title' => $this->randomMachineName(8),
          'type_of_alert' => $key,
          'moderation_state' => 'published',
          'changed' => (new DrupalDateTime('-1 hour'))->getTimestamp(),
        ]);
      $alert_entity->save();
      $alert_new[] = $alert_entity->id();
    }

    // Create and render the block and get the alert banner IDs as an array.
    $plugin_block = $block_manager->createInstance('localgov_alert_banner_block', $config);
    $render = $this->getBannersFromBlockRenderArray($plugin_block);
    $result_2 = [];
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
    $render = $this->getBannersFromBlockRenderArray($plugin_block);
    $result_3 = [];
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

  /**
   * Test alert banner block order without type of alert.
   */
  public function testAlertBannerBlockOrderWithoutTypeOfAlert() {

    // Delete type of alert field.
    // This is so we are testing the case where :-
    // - Alerts don't have a type, so are in date order.
    // - Querying for current banners without the type field is possible.
    $this->container
      ->get('entity_type.manager')
      ->getStorage('field_storage_config')
      ->load('localgov_alert_banner.type_of_alert')
      ->delete();

    // Alert times.
    $alert_times = [
      (new DrupalDateTime('-4 hours'))->getTimestamp(),
      (new DrupalDateTime('-2 hours'))->getTimestamp(),
      (new DrupalDateTime('-3 hours'))->getTimestamp(),
      (new DrupalDateTime('now'))->getTimestamp(),
    ];

    // Set up alert banners.
    $alert = [];
    foreach ($alert_times as $changed) {
      $alert_entity = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
        ->create([
          'type' => 'localgov_alert_banner',
          'title' => $this->randomMachineName(8),
          'moderation_state' => 'published',
          'changed' => $changed,
        ]);
      $alert_entity->save();
      $alert[] = $alert_entity->id();
    }

    // Create and render the block and get the alert banner IDs as an array.
    $block_manager = $this->container->get('plugin.manager.block');
    $config = [];
    $plugin_block = $block_manager->createInstance('localgov_alert_banner_block', $config);
    $render = $this->getBannersFromBlockRenderArray($plugin_block);
    $result = [];
    foreach ($render as $render_value) {
      $result[] = $render_value['#localgov_alert_banner']->id();
    }

    // Set expected order, which will be date changed order.
    $expected = [
      $alert[3],
      $alert[1],
      $alert[2],
      $alert[0],
    ];
    $this->assertEquals($expected, $result);
  }

  /**
   * Get banners from block render array.
   *
   * @param \Drupal\Core\Block\BlockPluginInterface $plugin_block
   *   Block plugin.
   *
   * @return array
   *   Block render array missing keys starting with #.
   */
  protected function getBannersFromBlockRenderArray(BlockPluginInterface $plugin_block): array {
    return array_filter($plugin_block->build(), function ($key) {
      return strpos($key, '#') !== 0;
    }, ARRAY_FILTER_USE_KEY);
  }

}
