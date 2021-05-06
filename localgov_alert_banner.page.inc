<?php

/**
 * @file
 * Contains localgov_alert_banner.page.inc.
 *
 * Page callback for Alert banner entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;

/**
 * Prepares variables for Alert banner templates.
 *
 * Default template: localgov_alert_banner.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 *   eg. To fetch the alert banner element,
 *   use variables['elements']['#localgov_alert_banner'].
 */
function template_preprocess_localgov_alert_banner(array &$variables) {

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }

  /** @var \Drupal\localgov_alert_banner\Entity\AlertBannerEntity $entity */
  $entity = $variables['elements']['#localgov_alert_banner'];

  // Set default link text if field exists.
  if ($entity->hasField('link')) {
    if ($entity->get('link')->title === '') {
      $variables['content']['link'] = Link::fromTextAndUrl(t('More information'), Url::fromUri($entity->get('link')->uri))->toString();
    }
  }

  // Non-content variables.
  $variables['display_title'] = $entity->get('display_title')->value;
  $variables['remove_hide_link'] = $entity->get('remove_hide_link')->value;
  // Type of alert is an optional field and may not exist.
  if ($entity->hasField('type_of_alert')) {
    $variables['type_of_alert'] = $entity->get('type_of_alert')->value;
  }

}

/**
 * Implements hook_preprocess_localgov_alert_banner() for hook_preprocess_localgov_alert_banner__full().
 *
 * - Adds a unique id for the top level wrapper div of the alert banner.
 * - Adds relevant Javascript settings and library.
 */
function localgov_alert_banner_preprocess_localgov_alert_banner__localgov_full_page(&$variables) {
  $alert_banner_id = $variables['attributes']['id'] ?? Html::getUniqueId('localgov-full-page-alert-banner');
  $variables['attributes']['id'] = $alert_banner_id;

  $variables['#attached']['drupalSettings']['localgov_alert_banner']['localgov_full_page_alert_banner_id'] = $alert_banner_id;
  $variables['#attached']['library'][] = 'localgov_alert_banner/full_page_alert_banner';
}
