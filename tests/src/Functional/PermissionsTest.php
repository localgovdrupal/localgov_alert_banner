<?php

namespace Drupal\Tests\localgov_alert_banner\Functional;

use Drupal\Tests\BrowserTestBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional tests for LocalGovDrupal Alert banner permissions.
 */
class PermissionsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

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

    // Set up an alert banner.
    $title = $this->randomMachineName(8);
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title,
        'short_description' => $alert_message,
        'type_of_alert' => 'minor',
        // 'link' => 'https://localgovdrupal.org/'.
      ]);
    $alert->save();

  }

  /**
   * Test the alert banner user access permissions.
   */
  public function testAlertBannerUserAccess() {

    // Check that anonymous user cannot access to the overview page.
    $this->drupalGet('admin/content/alert-banner');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    // Check that anonymous user does not have CRUD page access.
    $this->drupalGet('admin/content/alert-banner/add/localgov_alert_banner');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/1/edit');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/1/revisions');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/1/delete');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    // Check that anonymous user cannot view the alert banner main page.
    $this->drupalGet('admin/content/alert-banner/1');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    // Check that anonymous user cannot access the alert banner types.
    $this->drupalGet('admin/structure/alert-banner-types/localgov_alert_banner_type');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    $normalAdminUser = $this->createUser(['access administration pages']);
    $this->drupalLogin($normalAdminUser);

    // Check that authenticated user cannot access to the overview page.
    $this->drupalGet('admin/content/alert-banner');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    // Check that authenticated user does not have CRUD page access.
    $this->drupalGet('admin/content/alert-banner/add/localgov_alert_banner');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/1/edit');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/1/revisions');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/1/delete');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    // Check that authenticated user cannot view the alert banner main page.
    $this->drupalGet('admin/content/alert-banner/1');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    // Check that authenticated user cannot access the alert banner types.
    $this->drupalGet('admin/structure/alert-banner-types/localgov_alert_banner_type');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    $this->drupalLogout();

    $emergencyPublisherUser = $this->createUser();
    $emergencyPublisherUser->addRole('emergency_publisher');
    $emergencyPublisherUser->save();
    $this->drupalLogin($emergencyPublisherUser);

    // Check that emergency publisher user has access to the overview page.
    $this->drupalGet('admin/content/alert-banner');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Check that emergency publisher user has CRUD page access.
    $this->drupalGet('admin/content/alert-banner/add/localgov_alert_banner');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/1/edit');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/1/revisions');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/1/delete');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Check that emergency publisher user can view the alert banner main page.
    $this->drupalGet('admin/content/alert-banner/1');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Check that emergency publisher user cannot access the alert banner types.
    $this->drupalGet('admin/structure/alert-banner-types/localgov_alert_banner_type');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    $this->drupalLogout();

    // Test a user with only the per banner permissions.
    $bannerUser = $this->createUser([
      'access administration pages',
      'access localgov alert banner listing page',
      'manage localgov alert banner localgov_alert_banner entities',
      'view localgov alert banner localgov_alert_banner entities',
    ]);
    $this->drupalLogin($bannerUser);

    // Check that emergency publisher user has access to the overview page.
    $this->drupalGet('admin/content/alert-banner');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Check that emergency publisher user has CRUD page access.
    $this->drupalGet('admin/content/alert-banner/add/localgov_alert_banner');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/1/edit');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/1/revisions');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/1/delete');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Check that emergency publisher user can view the alert banner main page.
    $this->drupalGet('admin/content/alert-banner/1');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    $this->drupalLogout();

    // Test an admin user can access the alert banner types.
    $adminUser = $this->createUser([
      'access administration pages',
      'administer localgov alert banner types',
    ]);
    $this->drupalLogin($adminUser);

    // Check that the admin user can access the alert banner types.
    $this->drupalGet('admin/structure/alert-banner-types/localgov_alert_banner_type');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
  }

}
