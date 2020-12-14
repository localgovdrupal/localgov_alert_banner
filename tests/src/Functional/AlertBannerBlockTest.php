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
  protected $defaultTheme = 'classy';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'block',
    'path',
    'options',
    'localgov_alert_banner',
  ];

  /**
   * A user with the 'administer blocks' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser(['administer blocks']);
    $this->drupalLogin($this->adminUser);
    $this->drupalPlaceBlock('localgov_alert_banner_block');
    $this->drupalLogout($this->adminUser);
  }

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
        'short_description' => $alert_message,
        'type_of_alert' => 'minor',
        // 'link' => 'https://localgovdrupal.org/'.
        'status' => TRUE,
      ]);
    $alert->save();

    // Load the front page and check the banner is displayed.
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains($alert_message);
  }

  /**
   * Test non live alert banner does not display.
   */
  public function testNonLiveAlertBannerDoesNotDisplay() {
    // Set up an alert banner.
    $title = $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title,
        'short_description' => $alert_message,
        'type_of_alert' => 'minor',
        // 'link' => 'https://localgovdrupal.org/'.
        'status' => FALSE,
      ]);
    $alert->save();

    // Load the front page and check the banner is not displayed.
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextNotContains($alert_message);
  }

  /**
   * Test display title option.
   */
  public function testAlertDisplayTitle() {
    $title = $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title,
        'short_description' => $alert_message,
        'type_of_alert' => 'minor',
        'display_title' => 1,
        'status' => TRUE,
      ]);
    $alert->save();

    // Check title is shown when display title is true.
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains($title);

    // Check title is not shown when display title is false.
    $alert->set('display_title', ['value' => 0]);
    $alert->save();
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextNotContains($title);
  }

  /**
   * Test remove hide link option.
   */
  public function testAlertRemoveHideLink() {
    $title = $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title,
        'short_description' => $alert_message,
        'type_of_alert' => 'minor',
        'remove_hide_link' => 0,
        'status' => TRUE,
      ]);
    $alert->save();

    // Check hide link is shown when remove_hide_link is not set.
    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('js-alert-banner-close');
    $this->assertSession()->pageTextContains('Hide');

    // Check title is not shown when remove_hide_link is set.
    $alert->set('remove_hide_link', ['value' => 1]);
    $alert->save();
    $this->drupalGet('<front>');
    $this->assertSession()->responseNotContains('js-alert-banner-close');
  }

}
