<?php

namespace Drupal\group_welcome_message\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;

/**
 * Provides a 'GroupWelcomeMessageLocalActionsBlock' block.
 *
 * @Block(
 *  id = "group_welcome_message_actions_block",
 *  admin_label = @Translation("Group Welcome Message Actions block"),
 * )
 */
class GroupWelcomeMessageLocalActionsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * EventAddBlock constructor.
   *
   * @param array $configuration
   *   The given configuration.
   * @param string $plugin_id
   *   The given plugin id.
   * @param mixed $plugin_definition
   *   The given plugin definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $group = \Drupal::routeMatch()->getParameter('group');

    if ($group instanceOf GroupInterface) {

      $member = $group->getMember($account);
      
      if ($member) {
        if($member->hasPermission('edit group', $account)) {
          return AccessResult::allowed();
        }
      }
      elseif ($user->hasRole('administrator')) {
        return AccessResult::allowed()->cachePerUser();
      }
      else {            
        return AccessResult::forbidden()->cachePerUser();
      }
    }
    else {
      return AccessResult::forbidden()->cachePerUser();
    }

    return AccessResult::neutral();

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $cache_contexts = parent::getCacheContexts();    
    return $cache_contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();
    return $cache_tags;
  }

  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    // Get current group so we can build correct links.
    $group = \Drupal::routeMatch()->getParameter('group');

    if ($group instanceof GroupInterface) {

      $links = [
        'manage_welcome_message' => [
          '#type' => 'link',
          '#title' => $this->t('Manage Welcome Message'),
          '#url' => Url::fromRoute('entity.group_welcome_message.add_form', ['group' => $group->id()]
              ),
          '#attributes' => [
            'class' => ['group-welcome-message-action-button','button', 'btn','btn-default'],
          ],
        ],
        'import_group_members' => [
          '#type' => 'link',
          '#title' => $this->t('Import Group members'),
          '#url' => Url::fromRoute('group_member_import.import', ['group' => $group->id()]
              ),
          '#attributes' => [
            'class' => ['group-welcome-message-action-button','button', 'btn','btn-default'],
          ],
        ]
      ];

      $build['content'] = $links;
      $build['content']['#attached'] = [
        'library' => [
          'group_welcome_message/design',
        ],
      ];

    }

    return $build;

  }

}
