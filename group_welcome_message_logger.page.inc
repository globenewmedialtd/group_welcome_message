<?php

/**
 * @file
 * Contains group_welcome_message_logger.page.inc.
 *
 * Page callback for Welcome Message Logs entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Welcome Message Logs templates.
 *
 * Default template: group_welcome_message_logger.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_group_welcome_message_logger(array &$variables) {
  // Fetch SocialWelcomeMessageLogger Entity Object.
  $group_welcome_message_logger = $variables['elements']['#group_welcome_message_logger'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
