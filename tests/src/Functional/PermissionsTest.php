<?php

namespace Drupal\Tests\localgov_alert_banner\Functional;

use Drupal\Tests\BrowserTestBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional tests for LocalGovDrupal Alert banner block.
 */
class PermissionsTest extends BrowserTestBase {

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
   * {@inheritdoc}
   */
  public function setUp() {

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

    // Check that anonymous user cannot access to the overview page
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner');
    // @TODO alert banner new admin view.
    // $this->drupalGet('admin/content/alert-banners');
    $this->assertResponse(Response::HTTP_FORBIDDEN);

    // Check that anonymous user does not have CRUD page access
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner/add/localgov_alert_banner');
    $this->assertResponse(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner/1/edit');
    $this->assertResponse(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner/1/delete');
    $this->assertResponse(Response::HTTP_FORBIDDEN);

    // Check that anonymous user cannot view the alert banner main page.
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner/1');
    $this->assertResponse(Response::HTTP_FORBIDDEN);

    $normalAdminUser = $this->createUser(['access administration pages']);
    $this->drupalLogin($normalAdminUser);

    // Check that authenticated user cannot access to the overview page
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner');
    // @TODO alert banner new admin view.
    // $this->drupalGet('admin/content/alert-banners');
    $this->assertResponse(Response::HTTP_FORBIDDEN);

    // Check that authenticated user does not have CRUD page access
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner/add/localgov_alert_banner');
    $this->assertResponse(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner/1/edit');
    $this->assertResponse(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner/1/delete');
    $this->assertResponse(Response::HTTP_FORBIDDEN);

    // Check that authenticated user cannot view the alert banner main page.
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner/1');
    $this->assertResponse(Response::HTTP_FORBIDDEN);

    $emergencyPublisherUser = $this->createUser();
    $emergencyPublisherUser->addRole('emergency_publisher');
    $this->drupalLogin($emergencyPublisherUser);

    // Check that emergency publisher user has access to the overview page
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner');
    // @TODO alert banner new admin view.
    // $this->drupalGet('admin/content/alert-banners');
    $this->assertResponse(Response::HTTP_OK);

    // Check that emergency publisher user has CRUD page access
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner/add/localgov_alert_banner');
    $this->assertResponse(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner/1/edit');
    $this->assertResponse(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner/1/delete');
    $this->assertResponse(Response::HTTP_OK);

    // Check that emergency publisher user can view the alert banner main page.
    $this->drupalGet('admin/content/alert-banner/localgov_alert_banner/1');
    $this->assertResponse(Response::HTTP_OK);

  }

}
