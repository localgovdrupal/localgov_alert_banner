<?php

namespace Drupal\Tests\localgov_alert_banner\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\node\NodeInterface;

/**
 * Functional tests for LocalGov Drupal Alert banner admin view.
 */
class VisibilityTest extends BrowserTestBase {
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'localgov_alert_banner',
    'node',
    'path_alias',
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

    // Create a page for the alert banner.
    $this->createContentType(['type' => 'page']);
    $node = $this->createNode([
      'title' => 'Council tax',
      'type' => 'page',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->container->get('entity_type.manager')->getStorage('path_alias')->create([
      'path' => '/node/' . $node->id(),
      'alias' => '/council-tax',
    ])->save();

    // Create an alert banner.
    $title = 'Council tax - ' . $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title,
        'short_description' => $alert_message,
        'type_of_alert' => '20--minor',
        'moderation_state' => 'published',
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

    // Create a second page and banner to test banner display caches correctly.
    // @See https://github.com/localgovdrupal/localgov_alert_banner/issues/327
    $node2 = $this->createNode([
      'title' => 'Adult social care',
      'type' => 'page',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->container->get('entity_type.manager')->getStorage('path_alias')->create([
      'path' => '/node/' . $node2->id(),
      'alias' => '/adult-social-care',
    ])->save();

    $title2 = 'Adult social care - ' . $this->randomMachineName(8);
    $alert_message2 = 'Alert message: ' . $this->randomMachineName(16);
    $alert2 = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title2,
        'short_description' => $alert_message2,
        'type_of_alert' => '20--minor',
        'moderation_state' => 'published',
        'visibility' => [
          'conditions' => [
            'request_path' => [
              'pages' => '/adult-social-care',
              'negate' => 0,
            ],
          ],
        ],
      ]);
    $alert2->save();

    // Check the correct alert is on /adult-social-care.
    $this->drupalGet('/adult-social-care');
    $this->assertSession()->pageTextNotContains($title);
    $this->assertSession()->pageTextContains($title2);

    // Check the correct alert is on /council-tax.
    $this->drupalGet('/council-tax');
    $this->assertSession()->pageTextContains($title);
    $this->assertSession()->pageTextNotContains($title2);
  }

}
