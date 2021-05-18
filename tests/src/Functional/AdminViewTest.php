<?php

namespace Drupal\Tests\localgov_alert_banner\Functional;

use Drupal\Tests\BrowserTestBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Url;

/**
 * Functional tests for LocalGovDrupal Alert banner admin view.
 */
class AdminViewTest extends BrowserTestBase {

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
   * Test load the alert banner admin view.
   */
  public function testLoadAdminView() {
    // Create an admin user -- @todo move to set up.
    $adminUser = $this->createUser([], 'admintestuser', TRUE);
    $this->drupalLogin($adminUser);

    // Check can access the admin view dashboard.
    $this->drupalGet('admin/content/alert-banner');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Check this is the view by making sure certian view only text is present.
    // @todo Work out how to make sure this is the view path (Kernal test?).
    $this->assertSession()->responseContains('Manage Alert Banners');

    // Check that loading the collection URL loads the admin dashboard.
    $collectionUrl = Url::fromRoute('entity.localgov_alert_banner.collection');
    $this->drupalGet($collectionUrl);
    $this->assertSession()->addressEquals('admin/content/alert-banner');
  }

}
