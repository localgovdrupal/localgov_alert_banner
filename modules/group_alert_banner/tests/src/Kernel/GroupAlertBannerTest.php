<?php

namespace Drupal\Tests\group_alert_banner\Kernel;

use Drupal\Tests\group\Kernel\GroupKernelTestBase;

/**
 * Tests allocation of Group specific Alert banners.
 */
class GroupAlertBannerTest extends GroupKernelTestBase {

  /**
   * Tests that Group specific banners remain limited to their groups.
   *
   *  @requires module group
   * - Creates two groups.
   * - Creates two Alert banners.  One for each group.
   * - Loads all banners for each group.
   * - Verifies banners have been assigned to their respective groups as
   *   expected.
   */
  public function testBannerAllocation() {

    $group0 = $this->createGroup(['type' => 'test_group']);
    $group1 = $this->createGroup(['type' => 'test_group']);

    $alert_banner_for_group0 = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')->create([
      'type'             => 'localgov_alert_banner',
      'title'            => 'Alert banner for Group zero',
      'type_of_alert'    => '00--announcement',
      'moderation_state' => 'published',
    ]);
    $alert_banner_for_group0->save();

    $alert_banner_for_group1 = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')->create([
      'type'             => 'localgov_alert_banner',
      'title'            => 'Alert banner for Group one',
      'type_of_alert'    => '00--announcement',
      'moderation_state' => 'published',
    ]);
    $alert_banner_for_group1->save();

    // Allocate banners to their respective groups.
    $group0->addRelationship($alert_banner_for_group0, static::OUR_ALERT_BANNER_PLUGIN_ID);
    $group1->addRelationship($alert_banner_for_group1, static::OUR_ALERT_BANNER_PLUGIN_ID);

    // Finally, verify banner allocation.
    $banners_for_group0 = $group0->getRelatedEntities(static::OUR_ALERT_BANNER_PLUGIN_ID);
    $this->assertCount(1, $banners_for_group0);
    $first_banner_for_group0 = current($banners_for_group0);
    $this->assertEquals($alert_banner_for_group0->id(), $first_banner_for_group0->id());

    $banners_for_group1 = $group1->getRelatedEntities(static::OUR_ALERT_BANNER_PLUGIN_ID);
    $this->assertCount(1, $banners_for_group1);
    $first_banner_for_group1 = current($banners_for_group1);
    $this->assertEquals($alert_banner_for_group1->id(), $first_banner_for_group1->id());
  }

  /**
   * Installs necessary configs.
   */
  protected function setUp(): void {

    parent::setUp();

    $this->installEntitySchema('content_moderation_state');
    $this->installEntitySchema('localgov_alert_banner');
    $this->installConfig([
      'content_moderation',
      'group_alert_banner',
      'localgov_alert_banner',
      'group_alert_banner_test',
    ]);
  }

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'link',
    'condition_field',
    'content_moderation',
    'group_alert_banner',
    'group_alert_banner_test',
    'localgov_alert_banner',
    'views',
    'workflows',
  ];

  /**
   * This plugin relates group types to banner types.
   */
  const OUR_ALERT_BANNER_PLUGIN_ID = 'group_localgov_alert_banner:localgov_alert_banner';

}
