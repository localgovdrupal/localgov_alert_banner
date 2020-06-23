<?php

/**
 * @file
 * Contains localgov_alert_banner.page.inc.
 *
 * Page callback for Alert banner entities.
 */

use Drupal\Core\Render\Element;

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
}
