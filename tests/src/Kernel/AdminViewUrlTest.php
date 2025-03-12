<?php

namespace Drupal\Tests\localgov_alert_banner\Kernel;

use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;
use Drupal\views\Views;

/**
 * Test admin view url.
 *
 * @group localgov_alert_banner
 */
class AdminViewUrlTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'condition_field',
    'content_moderation',
    'field',
    'link',
    'localgov_alert_banner',
    'options',
    'system',
    'text',
    'user',
    'views',
    'workflows',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('content_moderation_state');
    $this->installEntitySchema('user');
    $this->installEntitySchema('workflow');
    $this->installEntitySchema('localgov_alert_banner');
    $this->installConfig([
      'content_moderation',
      'system',
      'localgov_alert_banner',
      'user',
    ]);
  }

  /**
   * Test admin view url.
   *
   * @throws \Exception
   */
  public function testAdminViewUrl(): void {

    $view = Views::getView('localgov_admin_manage_alert_banners');
    $view->setDisplay('localgov_alert_banner_admin_list');

    $view_url_route = $view->getUrl()->getRouteName();
    $url = Url::fromRoute($view_url_route);

    $this->assertEquals('/admin/content/alert-banner', $url->toString());
  }

}
