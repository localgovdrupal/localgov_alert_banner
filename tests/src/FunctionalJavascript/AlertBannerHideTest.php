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

    // Wait for the button to be visible and interactive.
    $this->assertSession()->waitForElementVisible('css', '.js-localgov-alert-banner__close');
    // Ensure the button is correctly identified.
    $this->assertSession()->elementExists('css', '.js-localgov-alert-banner__close');

    // Find and click hide link.
    $page = $this->getSession()->getPage();

    // Find the hide button.
    $button = $page->findButton('Hide');
    // Click the button.
    $button->click();

    // Check cookie set and banner not visible.
    $this->assertSession()->CookieExists('hide-alert-banner-token');
    $this->assertSession()->pageTextNotContains($alert_message);

    // Test on login page.
    $this->drupalGet('/user/login');
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
    $page = $this->getSession()->getPage();
    $button_1 = $page->find('css', '[data-dismiss-alert-token="' . $alert->getToken() . '"] button');
    $button_1->click();

    // Reload home page.
    $this->drupalGet('<front>');

    // Test that the first banner is dismmised,
    // but the second banner still present.
    $this->assertSession()->pageTextNotContains($alert_message);
    $this->assertSession()->pageTextContains($alert_message_2);

    // Click the second alert hide button.
    $page = $this->getSession()->getPage();
    $button_2 = $page->find('css', '[data-dismiss-alert-token="' . $alert_2->getToken() . '"] button');
    $button_2->click();

    // Reload home page.
    $this->drupalGet('<front>');

    // Test that both banners are now dismissed.
    $this->assertSession()->pageTextNotContains($alert_message);
    $this->assertSession()->pageTextNotContains($alert_message_2);

    // Set up a third alert banner.
    $title_3 = $this->randomMachineName(8);
    $alert_message_3 = 'Alert message: ' . $this->randomMachineName(16);
    $alert_3 = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title_3,
        'short_description' => $alert_message_3,
        'type_of_alert' => 'minor',
        'moderation_state' => 'published',
      ]);
    $alert_3->save();

    // Hide the banner on the /user/login page.
    // This is to test the cookie is set for the site and not just the path.
    // @see https://github.com/localgovdrupal/localgov_alert_banner/issues/401
    $this->drupalGet('/user/login');
    $page = $this->getSession()->getPage();
    $button_3 = $page->find('css', '[data-dismiss-alert-token="' . $alert_3->getToken() . '"] button');
    $button_3->click();

    // Reload home page.
    $this->drupalGet('<front>');

    // Test banner is not present.
    $this->assertSession()->pageTextNotContains($alert_message_3);

  }

}
