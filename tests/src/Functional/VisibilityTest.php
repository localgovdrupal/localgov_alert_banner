<?php

namespace Drupal\Tests\localgov_alert_banner\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Functional tests for LocalGov Drupal Alert banner admin view.
 */
class VisibilityTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'localgov_alert_banner',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalPlaceBlock('localgov_alert_banner_block');
  }

  /**
   * Test the visibility conditions for alert banners.
   */
  public function testAlertBannerVisibility() {

    // Create an alert banner.
    $title = $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title,
        'short_description' => $alert_message,
        'type_of_alert' => 'minor',
        'status' => TRUE,
        'visibility' => [
          'conditions' => [
            'request_path' => [
              'pages' => '/council-tax',
              'negate' => 0,
            ],
          ],
        ],
      ]);
    $alert->save();

    // Check it's not on front page.
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextNotContains($title);

    // Check it's on /council-tax.
    $this->drupalGet('/council-tax');
    $this->assertSession()->pageTextContains($title);

    // Check it's still not on front page.
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextNotContains($title);

    // Check it's still on /council-tax.
    $this->drupalGet('/council-tax');
    $this->assertSession()->pageTextContains($title);
  }

}
