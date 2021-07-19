<?php

namespace Drupal\Tests\localgov_alert_banner\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\scheduled_transitions\Entity\ScheduledTransition;

/**
 * Kernel test for scheduling transitions.
 *
 * @group localgov_workflows
 */
class SchedulingTest extends KernelTestBase {

  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'condition_field',
    'content_moderation',
    'dynamic_entity_reference',
    'field',
    'link',
    'options',
    'scheduled_transitions',
    'system',
    'text',
    'user',
    'views',
    'workflows',
    'localgov_alert_banner',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setup();

    $this->installEntitySchema('content_moderation_state');
    $this->installEntitySchema('user');
    $this->installEntitySchema('scheduled_transition');
    $this->installEntitySchema('workflow');
    $this->installEntitySchema('localgov_alert_banner');
    $this->installConfig([
      'content_moderation',
      'scheduled_transitions',
      'system',
      'views',
      'localgov_alert_banner',
    ]);
  }

  /**
   * Test scheduling alert banners.
   */
  public function testAlertBannerScheduling() {
    $alert_banner_storage = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner');
    $runner = $this->container->get('scheduled_transitions.runner');

    // Create an alert banner.
    $alert_banner = $alert_banner_storage->create([
      'type' => 'localgov_alert_banner',
      'title' => $this->randomMachineName(8),
      'type_of_alert' => '00--announcement',
      'moderation_state' => 'unpublished',
    ]);
    $alert_banner->save();
    $alert_banner_id = $alert_banner->id();
    $this->assertEquals(1, $alert_banner->getRevisionId());
    $this->assertEquals('unpublished', $alert_banner->moderation_state->value);

    // Publish alert banner on schedule.
    $scheduled_transition = ScheduledTransition::create([
      'entity' => $alert_banner,
      'entity_revision_id' => 1,
      'author' => 1,
      'workflow' => 'localgov_alert_banners',
      'moderation_state' => 'published',
      'transition_on' => (new \DateTime('1 Jan 2020 12am'))->getTimestamp(),
    ]);
    $scheduled_transition->save();
    $runner->runTransition($scheduled_transition);
    $alert_banner = $alert_banner_storage->load($alert_banner_id);
    $this->assertEquals(2, $alert_banner->getRevisionId());
    $this->assertEquals('published', $alert_banner->moderation_state->value);

    // Unpublish alert banner on schedule.
    $scheduled_transition = ScheduledTransition::create([
      'entity' => $alert_banner,
      'entity_revision_id' => 2,
      'author' => 1,
      'workflow' => 'localgov_alert_banners',
      'moderation_state' => 'unpublished',
      'transition_on' => (new \DateTime('1 Jan 2021 12am'))->getTimestamp(),
    ]);
    $scheduled_transition->save();
    $runner->runTransition($scheduled_transition);
    // It shouldn't be necessary to reset the cache after running a transition.
    $alert_banner_storage->resetCache([$alert_banner_id]);
    $alert_banner = $alert_banner_storage->load($alert_banner_id);
    $this->assertEquals(3, $alert_banner->getRevisionId());
    $this->assertEquals('unpublished', $alert_banner->moderation_state->value);
  }

}
