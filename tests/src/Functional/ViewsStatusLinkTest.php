<?php

namespace Drupal\Tests\localgov_alert_banner\Functional;

use Drupal\localgov_alert_banner\Entity\AlertBannerEntity;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the core Drupal\views\Plugin\views\StatusPageLink handler.
 *
 * @group localgov_alert_banner
 */
class ViewsStatusLinkTest extends BrowserTestBase {

  /**
   * An instance of the alert banner.
   *
   * @var \Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface
   */
  public $alert;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['localgov_alert_banner'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $emergencyPublisherUser = $this->createUser();
    $emergencyPublisherUser->addRole('emergency_publisher');
    $emergencyPublisherUser->save();
    $this->drupalLogin($emergencyPublisherUser);

    // Set up an alert banner.
    $title = $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $this->alert = AlertBannerEntity::create([
      'type' => 'localgov_alert_banner',
      'title' => $title,
      'short_description' => $alert_message,
      'type_of_alert' => 'minor',
      'status' => FALSE,
    ]);
    $this->alert->save();
  }

  /**
   * Tests entity link fields.
   */
  public function testEntityLink() {
    $this->drupalGet('/admin/content/alert-banner');
    $session = $this->assertSession();

    // Tests 'Link to Content'.
    $session->linkByHrefExists($this->alert->toUrl('status-form')->toString());
    $session->linkExists('Set banner live');
    $this->alert->status = TRUE;
    $this->alert->save();

    $this->drupalGet('/admin/content/alert-banner');
    $session->linkExists('Remove banner');
  }

}
