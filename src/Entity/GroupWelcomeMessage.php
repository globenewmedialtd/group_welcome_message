<?php

namespace Drupal\group_welcome_message\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Welcome Message entity.
 *
 * @ConfigEntityType(
 *   id = "group_welcome_message",
 *   label = @Translation("Welcome Message"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\group_welcome_message\GroupWelcomeMessageListBuilder",
 *     "form" = {
 *       "add" = "Drupal\group_welcome_message\Form\GroupWelcomeMessageForm",
 *       "edit" = "Drupal\group_welcome_message\Form\GroupWelcomeMessageForm",
 *       "delete" = "Drupal\group_welcome_message\Form\GroupWelcomeMessageDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\group_welcome_message\GroupWelcomeMessageHtmlRouteProvider",
 *     },
 * "access" = "Drupal\group_welcome_message\GroupWelcomeMessageAccessControlHandler",
 *   },
 *   config_prefix = "group_welcome_message",
 *   admin_permission = "manage group welcome messages",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/group_welcome_message/{group_welcome_message}",
 *     "add-form" = "/group/{group}/group_welcome_message/add",
 *     "edit-form" = "/admin/group_welcome_message/{group_welcome_message}/edit",
 *     "delete-form" = "/admin/group_welcome_message/{group_welcome_message}/delete",
 *   }
 * )
 */
class GroupWelcomeMessage extends ConfigEntityBase implements GroupWelcomeMessageInterface {

  /**
   * The Welcome Message ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Welcome Message label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Welcome Message subject.
   *
   * @var string
   */
  protected $subject;

  /**
   * The Welcome Message body.
   *
   * @var array
   */
  protected $body;

    /**
   * The Welcome Message body existing.
   *
   * @var array
   */
  protected $bodyExisting;

  /**
   * The Welcome Message group.
   *
   * @var string
   */
  protected $group;


  /**
   * {@inheritdoc}
   */
  public function getSubject() {
    return $this->subject;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubject(string $subject) {
    $this->subject = $subject;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    return $this->body;
  }

  /**
   * {@inheritdoc}
   */
  public function setBody(array $body) {
    $this->body = $body;
    return $this;
  }

    /**
   * {@inheritdoc}
   */
  public function getBodyExisting() {
    return $this->body_existing;
  }

  /**
   * {@inheritdoc}
   */
  public function setBodyExisting(array $body) {
    $this->body_existing = $body;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup() {
    return $this->group;
  }

  /**
   * {@inheritdoc}
   */
  public function setGroup(string $group) {
    $this->group = $group;
    return $this;
  }

}
