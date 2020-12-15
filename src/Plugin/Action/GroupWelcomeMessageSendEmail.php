<?php

namespace Drupal\group_welcome_message\Plugin\Action;


use Drupal\group\Entity\GroupContentInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\user\UserInterface;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsPreconfigurationInterface;
use Egulias\EmailValidator\EmailValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * An action for sending welcome messages.
 *
 * @Action(
 *   id = "group_welcome_message_send_email",
 *   label = @Translation("Send welcome message"),
 *   type = "group_content",
 *   confirm = TRUE,
 * )
 */
class GroupWelcomeMessageSendEmail extends ViewsBulkOperationsActionBase implements ContainerFactoryPluginInterface, ViewsBulkOperationsPreconfigurationInterface {

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The email validator.
   *
   * @var \Egulias\EmailValidator\EmailValidator
   */
  protected $emailValidator;

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queue;

  /**
   * TRUE if the current user can use the "Mail HTML" text format.
   *
   * @var bool
   */
  protected $allowTextFormat;

  /**
   * Constructs a GroupWelcomeMessageSendEmail object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Egulias\EmailValidator\EmailValidator $email_validator
   *   The email validator.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue factory.
   * @param bool $allow_text_format
   *   TRUE if the current user can use the "Mail HTML" text format.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Token $token, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger, LanguageManagerInterface $language_manager, EmailValidator $email_validator, QueueFactory $queue_factory, $allow_text_format) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->token = $token;
    $this->storage = $entity_type_manager;
    $this->logger = $logger;
    $this->languageManager = $language_manager;
    $this->emailValidator = $email_validator;
    $this->queue = $queue_factory;
    $this->allowTextFormat = $allow_text_format;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('token'),
      $container->get('entity_type.manager'),
      $container->get('logger.factory')->get('action'),
      $container->get('language_manager'),
      $container->get('email.validator'),
      $container->get('queue'),
      $container->get('current_user')->hasPermission('use text format mail_html')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setContext(array &$context) {
    parent::setContext($context);
    // @todo: make the batch size configurable.    
    $context['batch_size'] = 25;
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $objects) {
    // Array $objects contain all the entities of this bulk operation batch.
    // We want smaller queue items then this so we chunk these.
    // @todo: make the chunk size configurable or dependable on the batch size.
    $chunk_size = 10;
    $chunks = array_chunk($objects, $chunk_size);
    foreach ($chunks as $chunk) {
      $users = [];
      // The chunk items contain entities, we want to perform an action on this.
      foreach ($chunk as $entity) {
        // The action retrieves the user ID of the user.
        $users[] = $this->execute($entity);
      }

      // Get the entity ID of the email that is send.
      //$data['mail'] = $this->configuration['queue_storage_id'];
      // Add the list of user IDs.
      $data['users'] = $users;
      $data['group'] = $this->context['arguments'][0];
      
      //Group_id not in context anymore withou social!!
      //$data['group'] = $this->context['group_id'];
      

      // Put the $data in the queue item.
      /** @var \Drupal\Core\Queue\QueueInterface $queue */
      $queue = $this->queue->get('group_welcome_message_email_queue');
      $queue->createItem($data);

      // Get the queue storage entity and create a new entry.
      //$queue_storage = $this->storage->getStorage('queue_storage_entity');
      //$entity = $queue_storage->create([
        //'name' => 'social_welcome_message_email_queue',
        //'type' => 'email',
        //'finished' => FALSE
      //]);

      // When the new entity is saved, get the ID and save it within the bulk
      // operation action configuration.
      //if ($entity->save()) {
        //$this->configuration['queue_storage_id'] = $entity->id();
      //}


    }

    // Add a clarifying message.
    $this->messenger()->addMessage($this->t('The email(s) will be send in the background. You will be notified upon completion.'));
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\group\Entity\GroupContentInterface $entity */
    return $entity->getEntity()->id();
  }

  /**
   * Returns the email address of this account.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user object.
   *
   * @return string|null
   *   The email address, or NULL if the account is anonymous or the user does
   *   not have an email address.
   */
  public function getEmail(UserInterface $account) {
    return $account->getEmail();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPreConfigurationForm(array $form, array $values, FormStateInterface $form_state) {
  }



  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object instanceof GroupContentInterface) {
      /** @var \Drupal\group\Entity\GroupContentInterface $object */
      return $object->access('view', $account, $return_as_object);
    }

    return TRUE;
  }

}
