<?php

namespace Drupal\group_welcome_message\Plugin\QueueWorker;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\group_welcome_message\Entity\GroupWelcomeMessageInterface;
use Drupal\Core\Utility\Token;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupContentInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Component\Render\PlainTextOutput;
use Drupal\group_welcome_message\Entity\GroupWelcomeMessageLogger;
use Drupal\group_welcome_message\Entity\GroupWelcomeMessageLoggerInterface;
use Drupal\Core\Render\Markup;

/**
 * Queue worker to process email to users.
 *
 * @QueueWorker(
 *   id = "group_welcome_message_email_queue",
 *   title = @Translation("Group Welcome Message email processor"),
 *   cron = {"time" = 60}
 * )
 */
class GroupWelcomeMessageMailQueueProcessor extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;
  use StringTranslationTrait;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $storage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The language manager interface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Email validator service.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * The Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MailManagerInterface $mail_manager, EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation, Connection $database, LanguageManagerInterface $language_manager, EmailValidatorInterface $email_validator, Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mailManager = $mail_manager;
    $this->storage = $entity_type_manager;
    $this->connection = $database;
    $this->setStringTranslation($string_translation);
    $this->languageManager = $language_manager;
    $this->emailValidator = $email_validator;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.mail'),
      $container->get('entity_type.manager'),
      $container->get('string_translation'),
      $container->get('database'),
      $container->get('language_manager'),
      $container->get('email.validator'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Validate if the queue data is complete before processing.
    if (self::validateQueueItem($data)) {
      // Get the group and users.
      $group = $this->storage->getStorage('group')->load($data['group']);
      $users = $this->storage->getStorage('user')->loadMultiple($data['users']);

      //Check if all needed data available to process the item
      if ($group instanceof GroupInterface && !empty($users)) {
        \Drupal::logger('group_welcome_message')->notice('successful item processed');

        /** @var \Drupal\user\UserInterface $user */
        foreach ($users as $user) {
          // Get language of user
          $user_language = $user->language();
          $group_welcome_message_content = $this->getGroupWelcomeMessage($group,$user_language);

          // Attempt sending mail and send only if welcome message defined in user language
          if ($user->getEmail() && $group_welcome_message_content instanceof GroupWelcomeMessageInterface) {
            $this->sendMail($user->getEmail(), $user->language()->getId(), $group_welcome_message_content, $user, $group);
          }
        }
      }
      else {
        // Inform about the message not sent
        $batch_status_info = $this->t('Welcome Message not sent, because there is no Group and Users available');
        \Drupal::logger('group_welcome_message')->notice($batch_status_info);
      }
    }
  }

  /**
   * Send the email.
   *
   * @param string $user_mail
   *   The recipient email address.
   * @param string $langcode
   *   The recipient language.
   * @param \Drupal\group_welcome_message\Entity\GroupWelcomeMessageInterface $group_welcome_message
   *   The subject and body field from the GroupWelcomeMessage Entity
   * @param string $display_name
   *   In case of anonymous users a display name will be given.
   */
  protected function sendMail(string $user_mail, string $langcode, GroupWelcomeMessageInterface $group_welcome_message, $user, $group, $display_name = NULL) {

    // Send Emails from the configured site mail
    $site_mail = \Drupal::config('system.site')->get('mail');

    //$token_service = \Drupal::token();
    $token_context = array(
      'user' => $user,
      'group' => $group
    );

    $subject =  PlainTextOutput::renderFromHtml($this->token->replace($group_welcome_message->getSubject(), $token_context));
    $body = $this->token->replace($group_welcome_message->getBody()['value'], $token_context);
    $body_existing = $this->token->replace($group_welcome_message->getBodyExisting()['value'], $token_context);

    // Load user.module from the user module.
    module_load_include('module', 'user');

    $special_token_context = ['user' => $user];

    $special_token_options = [
      'langcode' => $langcode,
      'callback' => 'user_mail_tokens',
      'clear' => TRUE,
    ];

    $subject_special_tokens = PlainTextOutput::renderFromHtml($this->token->replace($subject, $special_token_context, $special_token_options));
    $body_special_tokens = $this->token->replace($body, $special_token_context, $special_token_options);
    $body_existing_special_tokens = $this->token->replace($body_existing, $special_token_context, $special_token_options);

    if ($user->getLastLoginTime() > 0 && !empty($body_existing)) {

      $body_special_tokens = $body_existing_special_tokens;

    }
    
    $context = [
      'subject' => $subject_special_tokens,
      'message' => Markup::create($body_special_tokens),
    ];

    if ($display_name) {
      $context['display_name'] = $display_name;
    }

    // Ensure html
    $context['params'] = array('format' => 'text/html');

    // Sending Email
    $delivered = $this->mailManager->mail('system', 'action_send_email', $user_mail, $langcode, [
      'context' => $context
    ]);

    if(!$delivered) {
      \Drupal::logger('group_welcome_message')->notice($user_mail . ' - ' . $this->t('not delivered!'));    
    }
    else {
      // Check if we have group_id and user_id already
      // So we can update existing logger
      if (!$insert = $this->getLoggerRecordUpdate($user, $group)) {

        $group_welcome_message_logger = GroupWelcomeMessageLogger::create([
          'type' => 'group_welcome_message_logger',
          'name' => 'Welcome Message Sent',
          'user_id' => ['target_id' => $user->id()],
          'group' => ['target_id' => $group->id()],
          'status' => 1
        ]);

        $group_welcome_message_logger->save();

      }

    }

  }

  /**
   * Check if this item is last.
   *
   * @param string $mail_id
   *   The email ID that is in the batch.
   *
   * @return int
   *   The remaining number.
   */
  protected function lastItem($mail_id) {
    // Escape the condition values.
    $item_type = $this->connection->escapeLike('mail');
    $item_id = $this->connection->escapeLike($mail_id);

    // Get all queue items from the queue worker.
    $query = $this->connection->select('queue', 'q');
    $query->fields('q', ['data', 'name']);
    // Plugin name is queue name.
    $query->condition('q.name', 'group_welcome_message_email_queue');
    // Add conditions for the item type and item mail id's.
    // This is not exact but an educated guess as there can be user id's in the
    // data that could contain the item id.
    $query->condition('q.data', '%' . $item_type . '%', 'LIKE');
    $query->condition('q.data', '%' . $item_id . '%', 'LIKE');
    $results = (int) $query->countQuery()->execute()->fetchField();

    // Return TRUE when last item.
    return !($results !== 1);
  }

  /**
   * Validate the queue item data.
   *
   * Before processing the queue item data we want to check if all the
   * necessary components are available.
   *
   * @param array $data
   *   The content of the queue item.
   *
   * @return bool
   *   True if the item contains all the necessary data.
   */
  private static function validateQueueItem(array $data) {
    // The queue data must contain the 'mail' key and it should either
    // contain 'users' or 'user_mail_addresses'.
    return isset($data['users']);
  }

  /**
   * Get the proper Welcome Message for the group
   *
   * Try to deliver a weclome message for the user language,
   * if available.
   *
   * If not deliver a welcome message for the default language
   * or False if there is no Welcome Message.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group object.
   *
   * @param string $user_language
   *   The language for the user
   *
   * @return \Drupal\group_welcome_message\Entity\GroupWelcomeMessageInterface $group_welcome_message
   *   or
   * @return bool FALSE
   */
  protected function getGroupWelcomeMessage(GroupInterface $group, LanguageInterface $language) {

    $group_welcome_message_id = FALSE;
    $group_welcome_message_content = FALSE;

    $query = $this->storage->getStorage('group_welcome_message')->getQuery();
    $query->condition('group', $group->id());


    $query->accessCheck(FALSE);

    $ids = $query->execute();

    if (!empty($ids)) {

      $group_welcome_messages = $this->storage->getStorage('group_welcome_message')->loadMultiple($ids);

      foreach ($group_welcome_messages as $group_welcome_message) {
        if ($group_welcome_message->getGroup() === $group->id()) {
          $group_welcome_message_id = $group_welcome_message->id();
        }
      }

      if ($group_welcome_message_id) {
        // Load our entity in users language or default
        $group_welcome_message = $this->storage->getStorage('group_welcome_message')
          ->load($group_welcome_message_id);
        $group_welcome_message_content = $this->getTranslatedConfigEntity($group_welcome_message,$language);


        if (!$group_welcome_message_content instanceof GroupWelcomeMessageInterface) {
          // Load default language
          $group_welcome_message_content = $this->storage->getStorage('group_welcome_message')
            ->load($group_welcome_message_id);
        }
      }
    }

    return $group_welcome_message_content;

  }

  /**
   * Get the translated Welcome Message for the group
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $configEntity
   *   The group object.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language interface for the user
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface $translatedConfigEntity
   */
  protected function getTranslatedConfigEntity(ConfigEntityInterface $configEntity, LanguageInterface $language) {
    $currentLanguage = $this->languageManager->getConfigOverrideLanguage();
    $this->languageManager->setConfigOverrideLanguage($language);
    $translatedConfigEntity = $this->storage
      ->getStorage($configEntity->getEntityTypeId())
      ->load($configEntity->id());
      $this->languageManager->setConfigOverrideLanguage($currentLanguage);

    return $translatedConfigEntity;
  }

  protected function getLoggerRecordUpdate(UserInterface $user, GroupInterface $group) {

    $insert = FALSE;

    $groupArray = ['target_id' => $group->id()];

    $query = $this->storage->getStorage('group_welcome_message_logger')->getQuery();
    $query->condition('user_id', $user->id());
    $query->condition('group', $group->id());
    $query->accessCheck(FALSE);

    $ids = $query->execute();
    $entities = $this->storage->getStorage('group_welcome_message_logger')->loadMultiple($ids);

    if (!empty($entities)) {

      $insert = TRUE;

    }


    return $insert;

  }


}
