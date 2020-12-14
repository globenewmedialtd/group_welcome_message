<?php

namespace Drupal\group_welcome_message\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Welcome Message Logs entity.
 *
 * @ingroup group_welcome_message
 *
 * @ContentEntityType(
 *   id = "group_welcome_message_logger",
 *   label = @Translation("Welcome Message Logs"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\group_welcome_message\GroupWelcomeMessageLoggerListBuilder",
 *     "views_data" = "Drupal\group_welcome_message\Entity\GroupWelcomeMessageLoggerViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\group_welcome_message\Form\GroupWelcomeMessageLoggerForm",
 *       "add" = "Drupal\group_welcome_message\Form\GroupWelcomeMessageLoggerForm",
 *       "edit" = "Drupal\group_welcome_message\Form\GroupWelcomeMessageLoggerForm",
 *       "delete" = "Drupal\group_welcome_message\Form\GroupWelcomeMessageLoggerDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\group_welcome_message\GroupWelcomeMessageLoggerHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\group_welcome_message\GroupWelcomeMessageLoggerAccessControlHandler",
 *   },
 *   base_table = "group_welcome_message_logger",
 *   translatable = FALSE,
 *   admin_permission = "administer welcome message logs entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/group-welcome-messages-log/group_welcome_message_logger/{group_welcome_message_logger}",
 *     "add-form" = "/admin/group-welcome-messages-log/group_welcome_message_logger/add",
 *     "edit-form" = "/admin/group-welcome-messages-log/group_welcome_message_logger/{group_welcome_message_logger}/edit",
 *     "delete-form" = "/admin/group-welcome-messages-log/group_welcome_message_logger/{group_welcome_message_logger}/delete",
 *     "collection" = "/admin/group-welcome-messages-log/group_welcome_message_logger",
 *   },
 *   field_ui_base_route = "group_welcome_message_logger.settings"
 * )
 */
class GroupWelcomeMessageLogger extends ContentEntityBase implements GroupWelcomeMessageLoggerInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

    /**
   * {@inheritdoc}
   */
  public function getGroup() {
    return $this->get('group')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setGroup($group) {
    $this->set('group', $group);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Welcome Message Logs entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);


    $fields['group'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Group'))
      ->setDescription(t('The group ID of group of the Welcome Message Logs entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'group')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);  



    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Welcome Message Logs entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Welcome Message Log has been published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
