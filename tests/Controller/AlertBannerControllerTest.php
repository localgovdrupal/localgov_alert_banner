<?php

namespace Drupal\bhcc_alert_banners\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the bhcc_alert_banners module.
 */
class AlertBannerControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "bhcc_alert_banners AlertBannerController's controller functionality",
      'description' => 'Test Unit for module bhcc_alert_banners and controller AlertBannerController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests bhcc_alert_banners functionality.
   */
  public function testAlertBannerController() {
    // Check that the basic functions of module bhcc_alert_banners.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
