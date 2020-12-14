<?php

namespace Drupal\group_welcome_message;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Welcome Message Logs entity.
 *
 * @see \Drupal\group_welcome_message\Entity\GroupWelcomeMessageLogger.
 */
class GroupWelcomeMessageLoggerAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\group_welcome_message\Entity\GroupWelcomeMessageLoggerInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished welcome message logs entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published welcome message logs entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit welcome message logs entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete welcome message logs entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add welcome message logs entities');
  }


}
