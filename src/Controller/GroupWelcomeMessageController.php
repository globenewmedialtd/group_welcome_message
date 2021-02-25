<?php

namespace Drupal\group_welcome_message\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;
use Drupal\group\Entity\GroupInterface;
use Drupal\group_welcome_message\Entity\GroupWelcomeMessage;
use Drupal\group_welcome_message\Entity\GroupWelcomeMessageInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Returns responses for Group Welcome Messages routes.
 */
class GroupWelcomeMessageController extends ControllerBase {

  /**
   * The tempstore service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * SocialWelcomeMessageController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory
   *   Private temporary storage factory.
   * @param \Drupal\views_bulk_operations\Service\ViewsBulkOperationsActionProcessorInterface $actionProcessor
   *   Views Bulk Operations action processor.
   */
  public function __construct(RequestStack $requestStack, PrivateTempStoreFactory $tempStoreFactory) {
    $this->requestStack = $requestStack;
    $this->tempStoreFactory = $tempStoreFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('tempstore.private')
    );
  }

  /**
   * Redirects from AddNew Forms to EditForms.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns a redirect to the social welcome message edit entity form.
   */
   public function redirectToEditForm() {

    $account = \Drupal::currentUser();

    $group_id = \Drupal::routeMatch()->getRawParameter('group');

    $group = Group::load($group_id);  

    $query = \Drupal::entityTypeManager()
      ->getStorage('group_welcome_message')
      ->getQuery();

    $query->condition('group', $group_id);

    $result = $query->execute();

    if ($result) {

      reset($result);
      $id = key($result);

      $group_welcome_message = \Drupal::entityTypeManager()
        ->getStorage('group_welcome_message')
        ->load($id);

      return $this->redirect('entity.group_welcome_message.edit_form', ['group' => $group_id, 'group_welcome_message' => $group_welcome_message->id()]);

    }

    $groupWelcomeMessageAddForm = \Drupal::entityTypeManager()
      ->getStorage('group_welcome_message')
      ->create();

    return \Drupal::service('entity.form_builder')->getForm($groupWelcomeMessageAddForm, 'add');

   }

   public function viewGroupWelcomeMessage() {

    $group_welcome_message = \Drupal::routeMatch()->getRawParameter('group_welcome_message');

    $entity = \Drupal::entityTypeManager()
      ->getStorage('group_welcome_message')
      ->load($group_welcome_message);

    $subject = [
      '#markup' => $entity->getSubject(),
    ];
      
    $body = [
      '#type' => 'processed_text',
      '#text' => $entity->getBody()['value'],
      '#format' => 'basic_html',
      // Potentially add keys for #filter_types_to_skip and #langcode.
    ];

    return $body;

   }

    /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account) {
    // Check permissions and combine that with any custom access checking needed. Pass forward
    // parameters from the route and/or request as needed.
   

  }


}
