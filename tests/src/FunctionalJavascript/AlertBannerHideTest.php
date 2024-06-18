<?php

namespace Drupal\Tests\localgov_alert_banner\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Functional tests for LocalGovDrupal Alert banner block.
 */
class AlertBannerHideTest extends WebDriverTestBase {

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
   * Test alert banner hide link.
   */
  public function testAlertBannerHide() {
    $this->drupalPlaceBlock('localgov_alert_banner_block');

    // Set up an alert banner.
    $title = $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title,
        'short_description' => $alert_message,
        'type_of_alert' => 'minor',
        'moderation_state' => 'published',
      ]);
    $alert->save();

    // Load the front page.
    $this->drupalGet('<front>');

    // Get session.
    $session = $this->getSession();

    // Find and click hide link.
    $page = $session->getPage();
    $this->assertSession()->waitForElementVisible('css', '.js-localgov-alert-banner__close');
    $button = $page->find('css', '[data-dismiss-alert-token="' . $alert->getToken() . '"] button');
    $this->assertNotEmpty($button);
    $button->click();

    // Check banner not visible.
    $this->assertSession()->pageTextNotContains($alert_message);

    // Test on login page.
    $this->drupalGet('/user');
    $this->assertSession()->pageTextNotContains($alert_message);

    // Update alert message.
    $title = $this->randomMachineName(8);
    $alert->set('title', ['value' => $title]);
    $alert->save();

    // Load the front page and check that banner displays and cookie token is
    // no longer valid.
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains($title);

    // Set up a second alert banner.
    $title_2 = $this->randomMachineName(8);
    $alert_message_2 = 'Alert message: ' . $this->randomMachineName(16);
    $alert_2 = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title_2,
        'short_description' => $alert_message_2,
        'type_of_alert' => 'minor',
        'moderation_state' => 'published',
      ]);
    $alert_2->save();

    // Check both banners are displayed.
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains($alert_message);
    $this->assertSession()->pageTextContains($alert_message_2);

    // Click the first alert hide button.
    $page = $session->getPage();
    $button_1 = $page->find('css', '[data-dismiss-alert-token="' . $alert->getToken() . '"] button');
    $button_1->click();

    // Reload home page.
    $this->drupalGet('<front>');

    // Test that the first banner is dismmised,
    // but the second banner still present.
    $this->assertSession()->pageTextNotContains($alert_message);
    $this->assertSession()->pageTextContains($alert_message_2);

    // Click the second alert hide button.
    $page = $session->getPage();
    $button_2 = $page->find('css', '[data-dismiss-alert-token="' . $alert_2->getToken() . '"] button');
    $button_2->click();

    // Reload home page.
    $this->drupalGet('<front>');

    // Test that both banners are now dismissed.
    $this->assertSession()->pageTextNotContains($alert_message);
    $this->assertSession()->pageTextNotContains($alert_message_2);

  }

}
