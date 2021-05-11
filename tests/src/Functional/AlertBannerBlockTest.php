<?php

namespace Drupal\Tests\localgov_alert_banner\Functional;

use Drupal\block\Entity\Block;
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
  protected static $modules = [
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
   * Place the alert block.
   *
   * Utility to place the alert banner block so we can assert
   * against its output.
   *
   * @param array $settings
   *   The block settings.
   *
   * @return \Drupal\block\Entity\Block
   *   The placed block.
   */
  protected function placeAlterBlock(array $settings = []) {
    $this->adminUser = $this->drupalCreateUser(['administer blocks']);
    $this->drupalLogin($this->adminUser);
    $block = $this->drupalPlaceBlock('localgov_alert_banner_block', $settings);
    $this->drupalLogout($this->adminUser);
    return $block;
  }

  /**
   * Update the settings of an existing block.
   *
   * @param \Drupal\block\Entity\Block $block
   *   The block to update.
   * @param array $newSettings
   *   The new settings to merge with the existing settings.
   */
  protected function updateBlockSettings(Block $block, array $newSettings) {
    $settings = $block->get('settings');
    $block->set('settings', $newSettings + $settings);
    $block->save();
  }

  /**
   * Test alert banner block displays.
   */
  public function testAlertBannerDisplays() {
    $this->placeAlterBlock();
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
    $this->placeAlterBlock();
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
    $this->placeAlterBlock();
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
    $this->placeAlterBlock();
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
    $this->assertSession()->responseContains('js-localgov-alert-banner__close');
    $this->assertSession()->pageTextContains('Hide');

    // Check title is not shown when remove_hide_link is set.
    $alert->set('remove_hide_link', ['value' => 1]);
    $alert->save();
    $this->drupalGet('<front>');
    $this->assertSession()->responseNotContains('js-localgov-alert-banner__close');
  }

  /**
   * Test types display option.
   */
  public function testAlertBannerTypeDisplay() {
    $alterType = $this->container->get('entity_type.manager')
      ->getStorage('localgov_alert_banner_type')
      ->create(['id' => 'extra_type', 'label' => 'Extra type']);
    $alterType->save();

    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => 'Alert of type localgov_alert_banner',
        'display_title' => 1,
        'status' => TRUE,
      ]);
    $alert->save();

    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'extra_type',
        'title' => 'Alert of type extra_type',
        'display_title' => 1,
        'status' => TRUE,
      ]);
    $alert->save();

    // No types selected should result in all types being displayed.
    $block = $this->placeAlterBlock();
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Alert of type localgov_alert_banner');
    $this->assertSession()->pageTextContains('Alert of type extra_type');

    $this->updateBlockSettings($block, [
      'include_types' => [
        'localgov_alert_banner' => 'localgov_alert_banner',
        'extra_type' => 'extra_type',
      ],
    ]);
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Alert of type localgov_alert_banner');
    $this->assertSession()->pageTextContains('Alert of type extra_type');

    $this->updateBlockSettings($block, [
      'include_types' => [
        'localgov_alert_banner' => 'localgov_alert_banner',
        'extra_type' => '0',
      ],
    ]);
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Alert of type localgov_alert_banner');
    $this->assertSession()->pageTextNotContains('Alert of type extra_type');

    $this->updateBlockSettings($block, [
      'include_types' => [
        'localgov_alert_banner' => '0',
        'extra_type' => 'extra_type',
      ],
    ]);
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextNotContains('Alert of type localgov_alert_banner');
    $this->assertSession()->pageTextContains('Alert of type extra_type');

  }

}
