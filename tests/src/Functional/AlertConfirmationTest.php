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
  protected static $modules = [
    'localgov_alert_banner',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
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

    // Set up a second alert banner.
    $title_2 = $this->randomMachineName(8);
    $alert_message_2 = 'Alert message: ' . $this->randomMachineName(16);
    $alert_2 = AlertBannerEntity::create([
      'type' => 'localgov_alert_banner',
      'title' => $title_2,
      'short_description' => $alert_message_2,
      'type_of_alert' => 'minor',
      'status' => TRUE,
    ]);
    $alert_2->save();

    // Go to the alert confirmation page, tick the unpublish others.
    // Verifiy that the alert 2 banner is unpublished.
    $this->drupalGet($alert->toUrl('status-form')->toString());
    $this->submitForm(['edit-unpublish-others' => 1], 'Confirm');

    // Check for the presence of the Put banner live link.
    $this->drupalGet($alert_2->toUrl('canonical')->toString());
    $this->assertSession()->pageTextContains('Put banner live');

    // Go to the second alert confirmation page, do not unpublih others.
    // Verifiy that the alert banner remains published.
    $this->drupalGet($alert_2->toUrl('status-form')->toString());
    $this->submitForm(['edit-unpublish-others' => 0], 'Confirm');

    // Check for the presence of the Remove banner link.
    $this->drupalGet($alert->toUrl('canonical')->toString());
    $this->assertSession()->pageTextContains('Remove banner');
  }

  /**
   * Test save alert with a state change redirects to the confimation page.
   */
  public function testSaveAlertRedirect() {

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

    // Set the banner live and verify redirect to confirm page.
    $edit_url = $alert->toUrl('edit-form')->toString();
    $form_vars = [
      'status-change' => 1,
    ];
    $this->drupalGet($edit_url);
    $this->submitForm($form_vars, 'Save');
    $this->assertSession()->addressEquals($alert->toUrl('status-form')->toString());
    $this->getSession()->getPage()->pressButton('Confirm');

    // Remove the banner live and verify redirect to confirm page.
    // Is this just the same operation? Duplicate test and can be removed?
    $edit_url = $alert->toUrl('edit-form')->toString();
    $form_vars = [
      'status-change' => 1,
    ];
    $this->drupalGet($edit_url);
    $this->submitForm($form_vars, 'Save');
    $this->assertSession()->addressEquals($alert->toUrl('status-form')->toString());
    $this->getSession()->getPage()->pressButton('Confirm');

    // Do not change the banner state and verify that user is not redirected to
    // the confirmation form page.
    $edit_url = $alert->toUrl('edit-form')->toString();
    $form_vars = [
      'status-change' => 0,
    ];
    $this->drupalGet($edit_url);
    $this->submitForm($form_vars, 'Save');
    $this->assertSession()->addressNotEquals($alert->toUrl('status-form')->toString());

    // Change the status of the banner with a destination paremeter and verify
    // that it still goes to the confirm form and then to the destination.
    $edit_url = $alert->toUrl('edit-form')->toString();
    $options = [
      'query' => [
        'destination' => '/admin',
      ],
    ];
    $form_vars = [
      'status-change' => 1,
    ];
    $this->drupalGet($edit_url, $options);
    $this->submitForm($form_vars, 'Save');
    $this->assertSession()->addressEquals($alert->toUrl('status-form')->toString());
    $this->getSession()->getPage()->pressButton('Confirm');
    $this->assertSession()->addressEquals('/admin');
  }

}
