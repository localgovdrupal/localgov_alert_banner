<?php

namespace Drupal\Tests\localgov_alert_banner\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\RoleInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional tests for LocalGovDrupal Alert banner permissions.
 */
class PermissionsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'localgov_alert_banner',
    'block',
    'user',
  ];

  /**
   * A user with the 'administer blocks' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {

    parent::setUp();

    // Set up an alert banner.
    $title = 'Alert of type localgov_alert_banner';
    $alert_message = 'Alert message: ' . $this->randomMachineName(16);
    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $title,
        'short_description' => $alert_message,
        'type_of_alert' => 'minor',
        'moderation_state' => 'published',
        // 'link' => 'https://localgovdrupal.org/'.
      ]);
    $alert->save();

    // Create a revision.
    $alert->setNewRevision(TRUE);
    $alert->revision_log = 'Created revision for alert banner ' . $title;
    $alert->save();

    // Extra type for applying new permissions.
    $alterType = $this->container->get('entity_type.manager')
      ->getStorage('localgov_alert_banner_type')
      ->create(['id' => 'extra_type', 'label' => 'Extra type']);
    $alterType->save();

    // Create an extra type alert banner.
    $alert_extra = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'extra_type',
        'title' => 'Alert of type extra_type',
        'display_title' => 1,
        'moderation_state' => 'published',
      ]);
    $alert_extra->save();

    // Place the alert banner block.
    $this->adminUser = $this->drupalCreateUser(['administer blocks']);
    $this->drupalLogin($this->adminUser);
    $this->drupalPlaceBlock('localgov_alert_banner_block', []);
    $this->drupalLogout();
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

    // Check the anonymous user cannot access revision pages.
    $this->drupalGet('admin/content/alert-banner/1/revisions/1/view');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/1/revisions/1/revert');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/1/revisions/1/delete');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    // Check that anonymous user cannot view the alert banner main page.
    $this->drupalGet('admin/content/alert-banner/1');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    // Check that anonymous user cannot access the alert banner types.
    $this->drupalGet('admin/structure/localgov_alert_banner_types');
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

    // Check the authenticated user cannot access revision pages.
    $this->drupalGet('admin/content/alert-banner/1/revisions/1/view');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/1/revisions/1/revert');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);
    $this->drupalGet('admin/content/alert-banner/1/revisions/1/delete');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    // Check that authenticated user cannot view the alert banner main page.
    $this->drupalGet('admin/content/alert-banner/1');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    // Check that authenticated user cannot access the alert banner types.
    $this->drupalGet('admin/structure/localgov_alert_banner_types');
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

    // Check the emergency publisher can access revision pages.
    $this->drupalGet('admin/content/alert-banner/1/revisions/1/view');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/1/revisions/1/revert');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/1/revisions/1/delete');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Check that emergency publisher user can view the alert banner main page.
    $this->drupalGet('admin/content/alert-banner/1');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Check that emergency publisher user cannot access the alert banner types.
    $this->drupalGet('admin/structure/localgov_alert_banner_types');
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    $this->drupalLogout();

    // Test a user with only the per banner permissions.
    $bannerUser = $this->createUser([
      'access administration pages',
      'access localgov alert banner listing page',
      'manage localgov alert banner localgov_alert_banner entities',
      'use localgov_alert_banners transition publish',
      'use localgov_alert_banners transition unpublish',
      'view localgov alert banner localgov_alert_banner entities',
      'view all localgov alert banner entity pages',
    ]);
    $this->drupalLogin($bannerUser);

    // Check that per banner test user has access to the overview page.
    $this->drupalGet('admin/content/alert-banner');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Check that per banner test user has CRUD page access.
    $this->drupalGet('admin/content/alert-banner/add/localgov_alert_banner');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/1/edit');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/1/revisions');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/1/delete');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Check the per banner test can access revision pages.
    $this->drupalGet('admin/content/alert-banner/1/revisions/1/view');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/1/revisions/1/revert');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);
    $this->drupalGet('admin/content/alert-banner/1/revisions/1/delete');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Check that per banner test user can view the alert banner main page.
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
    $this->drupalGet('admin/structure/localgov_alert_banner_types');
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    // Check user access of the banner itself can be restricted.
    // Reset the permissions.
    // Since installing the alert banner module gives access to all banners,
    // and each banner type, revoke the all banner entities and the extra_type
    // entities, anon user will still have access the the main banner.
    user_role_revoke_permissions(RoleInterface::ANONYMOUS_ID, [
      'view all localgov alert banner entities',
      'view localgov alert banner extra_type entities',
    ]);

    // Revoke the all entity permission from auth user.
    // They should still have access to both banners.
    user_role_revoke_permissions(RoleInterface::AUTHENTICATED_ID, [
      'view all localgov alert banner entities',
    ]);

    // Test that anon user can only view main alert banner,
    // and not the extra type.
    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Alert of type localgov_alert_banner');
    $this->assertSession()->pageTextNotContains('Alert of type extra_type');

    // Test that authenticated user can see all alert banners.
    $authUser = $this->createUser();
    $this->drupalLogin($authUser);
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Alert of type localgov_alert_banner');
    $this->assertSession()->pageTextContains('Alert of type extra_type');

    // Revoke access to the two banner types.
    // Now auth user should not see any baner.
    user_role_revoke_permissions(RoleInterface::AUTHENTICATED_ID, [
      'view localgov alert banner localgov_alert_banner entities',
      'view localgov alert banner extra_type entities',
    ]);

    $this->drupalGet('<front>');
    $this->assertSession()->pageTextNotContains('Alert of type localgov_alert_banner');
    $this->assertSession()->pageTextNotContains('Alert of type extra_type');

  }

}
