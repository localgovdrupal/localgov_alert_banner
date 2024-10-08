<?php

namespace Drupal\Tests\localgov_alert_banner\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\NodeInterface;

/**
 * Functional tests for LocalGovDrupal Alert banner block.
 */
class TranslationsTest extends BrowserTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'content_translation',
    'path',
    'path_alias',
    'options',
    'node',
    'language',
    'locale',
    'localgov_alert_banner',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalPlaceBlock('localgov_alert_banner_block');
    ConfigurableLanguage::createFromLangcode('zz')->save();
    $this->container->get('content_translation.manager')->setEnabled('localgov_alert_banner', 'localgov_alert_banner', TRUE);
    FieldConfig::loadByName('localgov_alert_banner', 'localgov_alert_banner', 'short_description')->setTranslatable(TRUE)->save();
  }

  /**
   * Test that valid translation is brought back based on current language.
   */
  public function testAlertBannerTranslation(): void {

    $default_langcode = $this->container->get('language.default')->get()->getId();

    // Create alert banner.
    $alert_title = 'home page alert title - ' . $this->randomMachineName(8);
    $alert_body = 'home page alert body - ' . $this->randomMachineName(32);
    $alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $alert_title,
        'short_description' => $alert_body,
        'type_of_alert' => 'minor',
        'moderation_state' => 'published',
        'langcode' => $default_langcode,
      ]);
    $alert->save();

    // Create translation.
    $translated_alert_title = 'translated home page alert title - ' . $this->randomMachineName(8);
    $translated_alert_body = 'translated home page alert body - ' . $this->randomMachineName(32);
    $alert->addTranslation('zz', [
      'title' => $translated_alert_title,
      'short_description' => $translated_alert_body,
      'type_of_alert' => 'minor',
      'moderation_state' => 'published',
    ])->save();

    // Test on home page.
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains($alert_title);
    $this->assertSession()->pageTextContains($alert_body);
    $this->assertSession()->pageTextNotContains($translated_alert_title);
    $this->assertSession()->pageTextNotContains($translated_alert_body);

    // Switch language.
    $this->drupalGet('/zz');

    // Test correct translation on home page.
    $this->assertSession()->pageTextNotContains($alert_title);
    $this->assertSession()->pageTextNotContains($alert_body);
    $this->assertSession()->pageTextContains($translated_alert_title);
    $this->assertSession()->pageTextContains($translated_alert_body);

    // Create node.
    $this->drupalCreateContentType(['type' => 'page']);
    $page = $this->createNode([
      'type' => 'page',
      'title' => $this->randomMachineName(8),
      'status' => NodeInterface::PUBLISHED,
      'langcode' => $default_langcode,
    ]);

    // Add node translation.
    $page->addTranslation('zz', [
      'title' => $this->randomMachineName(8),
      'status' => NodeInterface::PUBLISHED,
    ]);

    // Create banner with page restriction.
    $alert_title_node = 'node 1 alert title - ' . $this->randomMachineName(8);
    $alert_body_node = 'node 1 alert body - ' . $this->randomMachineName(32);
    $node_alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $alert_title_node,
        'short_description' => $alert_body_node,
        'type_of_alert' => 'minor',
        'moderation_state' => 'published',
        'langcode' => $default_langcode,
        'visibility' => [
          'conditions' => [
            'request_path' => [
              'pages' => '/node/1',
              'negate' => 0,
            ],
          ],
        ],
      ]);
    $node_alert->save();

    // Translate banner with page restriction.
    $translated_alert_title_node = 'translated node 1 alert title - ' . $this->randomMachineName(8);
    $translated_alert_body_node = 'translated node 1 alert body - ' . $this->randomMachineName(32);
    $node_alert->addTranslation('zz', [
      'title' => $translated_alert_title_node,
      'short_description' => $translated_alert_body_node,
      'type_of_alert' => 'minor',
      'moderation_state' => 'published',
    ])->save();

    // Go to node in default language.
    $this->drupalGet('/node/1');

    // Test correct translation appears.
    $this->assertSession()->pageTextContains($alert_title_node);
    $this->assertSession()->pageTextContains($alert_body_node);
    $this->assertSession()->pageTextNotContains($translated_alert_title_node);
    $this->assertSession()->pageTextNotContains($translated_alert_body_node);

    // Change language.
    $this->drupalGet('/zz/node/1');

    // Test correct translation appears.
    $this->assertSession()->pageTextNotContains($alert_title_node);
    $this->assertSession()->pageTextNotContains($alert_body_node);
    $this->assertSession()->pageTextContains($translated_alert_title_node);
    $this->assertSession()->pageTextContains($translated_alert_body_node);

    // Create node with path.
    $page = $this->createNode([
      'type' => 'page',
      'title' => $this->randomMachineName(8),
      'status' => NodeInterface::PUBLISHED,
      'langcode' => $default_langcode,
    ]);
    $this->container->get('entity_type.manager')->getStorage('path_alias')->create([
      'path' => '/node/' . $page->id(),
      'alias' => '/untranslated-path',
      'langcode' => $default_langcode,
    ])->save();

    // Add node translation.
    $page->addTranslation('zz', [
      'title' => $this->randomMachineName(8),
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->container->get('entity_type.manager')->getStorage('path_alias')->create([
      'path' => '/node/' . $page->id(),
      'alias' => '/translated-path',
      'langcode' => 'zz',
    ])->save();

    // Enable translation of the visibility field.
    FieldConfig::loadByName('localgov_alert_banner', 'localgov_alert_banner', 'visibility')->setTranslatable(TRUE)->save();

    // Create banner with page restriction.
    $alert_title_pa_node = 'path alias page alert title - ' . $this->randomMachineName(8);
    $alert_body_pa_node = 'path alias page alert body - ' . $this->randomMachineName(32);
    $pa_node_alert = $this->container->get('entity_type.manager')->getStorage('localgov_alert_banner')
      ->create([
        'type' => 'localgov_alert_banner',
        'title' => $alert_title_pa_node,
        'short_description' => $alert_body_pa_node,
        'type_of_alert' => 'minor',
        'moderation_state' => 'published',
        'langcode' => $default_langcode,
        'visibility' => [
          'conditions' => [
            'request_path' => [
              'pages' => '/untranslated-path',
              'negate' => 0,
            ],
          ],
        ],
      ]);
    $node_alert->save();

    // Translate banner with page restriction.
    $translated_alert_title_pa_node = 'translated path alias page alert title - ' . $this->randomMachineName(8);
    $translated_alert_body_pa_node = 'translated path alias page alert body - ' . $this->randomMachineName(32);
    $pa_node_alert->addTranslation('zz', [
      'title' => $translated_alert_title_pa_node,
      'short_description' => $translated_alert_body_pa_node,
      'type_of_alert' => 'minor',
      'moderation_state' => 'published',
    ])->save();

    // Go to node in default language.
    $this->drupalGet('/untranslated-path');

    // Test correct translation appears.
    $this->assertSession()->pageTextContains($alert_title_pa_node);
    $this->assertSession()->pageTextContains($alert_body_pa_node);
    $this->assertSession()->pageTextNotContains($translated_alert_title_pa_node);
    $this->assertSession()->pageTextNotContains($translated_alert_body_pa_node);

    // Change language.
    $this->drupalGet('/zz/translated-path');

    // Test correct translation appears.
    $this->assertSession()->pageTextNotContains($alert_title_pa_node);
    $this->assertSession()->pageTextNotContains($alert_body_pa_node);
    $this->assertSession()->pageTextContains($translated_alert_title_pa_node);
    $this->assertSession()->pageTextContains($translated_alert_body_pa_node);
  }

}
