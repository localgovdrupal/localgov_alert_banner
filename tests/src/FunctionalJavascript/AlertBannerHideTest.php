<?php

namespace Drupal\Tests\localgov_alert_banner\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Functional tests for LocalGovDrupal Alert banner block.
 */
class AlertBannerHideJavascriptTest extends WebDriverTestBase {

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
   * Test alert banner hide link.
   */
  public function testAlertBannerHide() {
    // Set up an alert banner.
    $title = $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title,
        'short_description' => $alert_message,
        'type_of_alert' => 'minor',
      ]);
    $alert->save();

    // Flag the alert banner to put it live.
    $flag_service = $this->container->get('flag');
    $flag = $flag_service->getFlagById('localgov_put_live');
    $flag_service->flag($flag, $alert);

    // Load the front page
    $this->drupalGet('<front>');

    // Find and click hide link
    $page = $this->getSession()->getPage();
    $link = $page->findLink('Hide');
    $this->assertNotEmpty($link);
    $link->click();

    // Check cookie set and banner not visible
    $this->assertSession()->CookieExists('hide-alert-banner-token');
    $this->assertSession()->pageTextNotContains($alert_message);

    // Test on login page
    $this->drupalGet('/user');
    $this->assertSession()->pageTextNotContains($alert_message);

    // Update alert message
    $title = $this->randomMachineName(8);
    $alert->set('title', ['value' => $title]);
    $alert->save();

    // Load the front page and check that banner displays and cookie token is no longer valid
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains($title);

  }

}
