<?php

namespace Drupal\group_welcome_message\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * @package Drupal\group_welcome_message\Routing
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Add controller for specific route
    if ($route = $collection->get('entity.group_welcome_message.add_form')) {
      $route->setDefault('_controller', '\Drupal\group_welcome_message\Controller\GroupWelcomeMessageController::redirectToEditForm');
      //$route->setRequirement('_access', TRUE);
    }
    if ($route = $collection->get('entity.group_welcome_message.canonical')) {
      $route->setDefault('_controller', '\Drupal\group_welcome_message\Controller\GroupWelcomeMessageController::viewGroupWelcomeMessage');
    }
    if ($route = $collection->
      get('entity.group_welcome_message.canonical')) {
      //$route->setRequirement('_permission', 'translate welcome messages');
    }

  }

}
