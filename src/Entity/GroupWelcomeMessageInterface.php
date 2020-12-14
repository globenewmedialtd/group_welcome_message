<?php

namespace Drupal\group_welcome_message\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Welcome Message entities.
 */
interface GroupWelcomeMessageInterface extends ConfigEntityInterface {

  // Add get/set methods for your configuration properties here.
  public function getSubject();

  public function setSubject(string $subject);

  public function getBody();

  public function setBody(array $body);

  public function getBodyExisting();

  public function setBodyExisting(array $body);

  public function getGroup();

  public function setGroup(string $group);


}
