<?php

namespace Drupal\Tests\localgov_alert_banner\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional tests for LocalGovDrupal Alert banner admin view.
 */
class AdminViewTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * Admin user.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'localgov_alert_banner',
  ];

  /**
   * Test setup.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp(): void {
    parent::setUp();

    // Create an admin user.
    $this->adminUser = $this->createUser([], 'admintestuser', TRUE);
  }

  /**
   * Test load the alert banner admin view.
   */
  public function testLoadAdminView() {
    $this->drupalLogin($this->adminUser);

    // Check can access the admin view dashboard.
    $this->drupalGet('admin/content/alert-banner');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Check this is the view by making sure certian view only text is present.
    $this->assertSession()->responseContains('Manage Alert Banners');

    // Check that loading the collection URL loads the admin dashboard.
    $collectionUrl = Url::fromRoute('entity.localgov_alert_banner.collection');
    $this->drupalGet($collectionUrl);
    $this->assertSession()->addressEquals('admin/content/alert-banner');
  }

}
