<?php

namespace Drupal\Tests\localgov_alert_banner\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Functional tests for LocalGovDrupal Alert banner block.
 */
class AlertBannerBlockTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'localgov_theme';

  /**
   * {@inheritdoc}
   */
  protected $profile = 'localgov';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'localgov_alert_banner',
  ];

  /**
   * Test alert banner block displays.
   */
  public function testAlertBannerDisplays() {
    // Set up an alert banner.
    $title = $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title,
        'field_localgov_short_description' => $alert_message,
        'field_localgov_type_of_alert' => 'minor',
        // 'field_localgov_link' => 'https://localgovdrupal.org/'.
      ]);
    $alert->save();

    // Flag the alert banner to put it live.
    $flag_service = $this->container->get('flag');
    $flag = $flag_service->getFlagById('localgov_put_live');
    $flag_service->flag($flag, $alert);

    // Load the front page and check the banner is displayed.
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains($alert_message);
  }

  /**
   * Test non live alert banner does not diaplay.
   */
  public function testNonLiveAlertBannerDoesNotDisplay() {
    // Set up an alert banner.
    $title = $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title,
        'field_localgov_short_description' => $alert_message,
        'field_localgov_type_of_alert' => 'minor',
        // 'field_localgov_link' => 'https://localgovdrupal.org/'.
      ]);
    $alert->save();

    // Load the front page and check the banner is not displayed
    // (will not be flagged).
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextNotContains($alert_message);
  }

}
