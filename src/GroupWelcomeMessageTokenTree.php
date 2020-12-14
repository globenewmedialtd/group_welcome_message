<?php

namespace Drupal\group_welcome_message;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

class GroupWelcomeMessageTokenTree extends ServiceProviderBase {

  public function alter ( ContainerBuilder $container ) {

    $definition = $container->getDefinition ( 'token.tree_builder' );
    $definition->setClass ( 'Drupal\group_welcome_message\GroupWelcomeMessageTokenTreeBuilder' );
  
  }
}
