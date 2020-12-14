<?php

namespace Drupal\group_welcome_message\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Welcome Message Logs entities.
 */
class GroupWelcomeMessageLoggerViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.




    return $data;
  }

}
