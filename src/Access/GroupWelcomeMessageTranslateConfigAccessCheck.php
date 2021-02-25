<?php

namespace Drupal\group_welcome_message\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Determines access to for translating group welcome messages.
 */
class GroupWelcomeMessageTranslateConfigAccessCheck implements AccessInterface {

  /**
   * Checks access to the group welcome message translate routes.
   */
  public function access(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, "translate group welcome messages");
  }

}