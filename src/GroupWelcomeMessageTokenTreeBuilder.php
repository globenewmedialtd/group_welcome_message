<?php

namespace Drupal\group_welcome_message;

use Drupal\token\TreeBuilder;

class GroupWelcomeMessageTokenTreeBuilder extends TreeBuilder {
  
  public function buildRenderable ( array $token_types, array $options = [] ) {
    
    $unrendered_tree = parent::buildRenderable ( $token_types, $options );
 
    foreach ( $unrendered_tree['#token_tree'] as 
    	$token_type => $token_type_value ) {

    	if ( in_array($token_type, $token_types) ) {

    		 foreach ( $unrendered_tree['#token_tree'][$token_type]['tokens'] as
    		 	$key => $value) {

    		 	if ( !in_array($key, $options['whitelist']) ) {

    				unset($unrendered_tree['#token_tree'][$token_type]['tokens'][$key]);

    			}
    		}
			}
    }
    
    return $unrendered_tree;

  }
}
