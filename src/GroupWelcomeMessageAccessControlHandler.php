<?php

namespace Drupal\group_welcome_message;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Drupal\user\Entity\User;


/**
 * Access controller for the group_welcome_message Entity.
 *
 * @see \Drupal\group_welcome_message\Entity\GroupWelcomeMessage.
 */
class GroupWelcomeMessageAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    // Load the user for Role check
    $user = User::load($account->id());

    switch ($operation) {
      case 'view':
       // Here we can access the getGroup()
       $group_id = $entity->getGroup();
       $group = Group::load($group_id); 

      if ($group) {

          $member = $group->getMember($account);

          if ($member) {
            if($member->hasPermission('edit group', $account)) {
              return AccessResult::allowedIfHasPermission($account, 'manage group welcome messages');
            }
          }
          elseif ($user->hasRole('administrator')) {
            return AccessResult::allowedIfHasPermission($account, 'manage group welcome messages')->cachePerUser();
          }

        }

        
      case 'update':    

        $user = User::load($account->id());
        $group_id = $entity->getGroup();
        $group = Group::load($group_id); 

        if ($group) {

          $member = $group->getMember($account);

          if ($member) {
            if($member->hasPermission('edit group', $account)) {
              return AccessResult::allowedIfHasPermission($account, 'manage group welcome messages');
            }
          }
          elseif ($user->hasRole('administrator')) {
            return AccessResult::allowedIfHasPermission($account, 'manage group welcome messages')->cachePerUser();
          }

        }

        return AccessResult::forbidden();

      case 'delete':

        // Users with 'cancel account' permission can cancel their own account.
        //return AccessResult::allowedIf($account_client == $entity_client)
          //->cachePerUser();
    }

    // No opinion.
    return AccessResult::neutral();


  }


  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {

    // Load the user for Role check
    $user = User::load($account->id());

    $group_id = \Drupal::routeMatch()->getRawParameter('group');
    $group = Group::load($group_id);

    if ($group) {

      $member = $group->getMember($account);

      if ($member) {
        if($member->hasPermission('edit group', $account)) {
          return AccessResult::allowedIfHasPermission($account, 'manage group welcome messages');
        }
      }
      elseif ($user->hasRole('administrator')) {
        return AccessResult::allowedIfHasPermission($account, 'manage group welcome messages');
      }

    }

    return AccessResult::forbidden();

  }
    

}
