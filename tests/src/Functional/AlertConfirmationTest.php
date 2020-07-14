<?php

namespace Drupal\Tests\localgov_alert_banner\Functional;

use Drupal\localgov_alert_banner\Entity\AlertBannerEntity;
use Drupal\Tests\BrowserTestBase;

/**
 * Functional tests for LocalGovDrupal Alert banner confirmation form.
 *
 * @group localgov_alert_banner
 */
class AlertConfirmationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'localgov_alert_banner',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $emergencyPublisherUser = $this->createUser();
    $emergencyPublisherUser->addRole('emergency_publisher');
    $emergencyPublisherUser->save();
    $this->drupalLogin($emergencyPublisherUser);
  }

  /**
   * Test alert banner publish un/publish confirmation.
   */
  public function testAlertConfirmation() {
    // Set up an alert banner.
    $title = $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert = AlertBannerEntity::create([
      'type' => 'localgov_alert_banner',
      'title' => $title,
      'short_description' => $alert_message,
      'type_of_alert' => 'minor',
      'status' => FALSE,
    ]);
    $alert->save();

    $this->drupalGet($alert->toUrl('canonical')->toString());
    $this->assertSession()->pageTextContains($alert_message);

    $this->clickLink('Put banner live');
    $this->assertSession()->addressEquals($alert->toUrl('status-form')->toString());
    $this->assertSession()->pageTextContains('Set the following alert banner live');
    $this->assertSession()->pageTextContains($alert_message);

    $this->getSession()->getPage()->pressButton('Confirm');
    $this->assertSession()->pageTextContains('The alert banner ' . $title . ' has been set live');

    $this->drupalGet($alert->toUrl('canonical')->toString());
    $this->clickLink('Remove banner');
    $this->assertSession()->addressEquals($alert->toUrl('status-form')->toString());
    $this->assertSession()->pageTextContains('Remove current alert banner ' . $title);
    $this->getSession()->getPage()->pressButton('Confirm');
    $this->assertSession()->pageTextContains('The alert banner ' . $title . ' has been removed.');
  }

}
