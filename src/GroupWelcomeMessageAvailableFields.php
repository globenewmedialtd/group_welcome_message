<?php

namespace Drupal\group_welcome_message;


class GroupWelcomeMessageAvailableFields {
  
  public function getAvailableFields() {

    $group_field_definitions = [];
    $available_fields = []; 
    $group_bundles = [];
    $profile_bundles = []; 
    $user_bundles = ['user'];
    $forbidden_group_fields = [
      'type',
      'field_group_allowed_join_method',
      'field_group_allowed_visibility'
    ]; 
    $forbidden_profile_fields = [
      'type'
    ];
    $forbidden_user_fields = [
      'init',
      'name'
    ];

    $default_tokens = [
      '[group]' => '[group]', 
      '[group:title]' => '[group:title]',     
      '[user:mail]' => '[user:mail]',
      '[user:one-time-login-url]' => '[user:one-time-login-url]',
      '[user:display-name]' => '[user:display-name]',
      '[site:name]' => '[site:name]'
    ];




      
    // Get group bundles
    $group_bundles = $this->getBundle('group');

    if (isset($group_bundles) && !empty($group_bundles)) {

      $available_group_fields = $this->getFields('group',$group_bundles,$forbidden_group_fields);

    }

    // Get profile bundles
    $profile_bundles = $this->getBundle('profile');

    // Default Tokens for user profiles
    $default_tokens_user_profile = $this->getUserProfileBundleTokens($profile_bundles);

    if (isset($profile_bundles) && !empty($profile_bundles)) {

      $available_profile_fields = $this->getFields('profile',$profile_bundles,$forbidden_profile_fields);


    }

    $available_user_fields = $this->getFields('user',$user_bundles,$forbidden_user_fields);

    $available_fields = array_merge($default_tokens,
                                    $default_tokens_user_profile,
                                    $available_group_fields, 
                                    $available_profile_fields,
                                    $available_user_fields);


      
    // Get user field definitions
    
    return $available_fields;

  }

  protected function getUserProfileBundleTokens($profile_bundles) {

    $token_fields = [];

    foreach ($profile_bundles as $bundle_key => $bundle) {
      $token_fields['[user:' . $bundle_key .']'] = '[user:' . $bundle_key . ']';
    }

    return $token_fields;

  }

  protected function getFields(string $entity_type, array $bundles, array $forbidden_fields) {

    $field_definitions = [];
    $available_fields = [];
    


    foreach ($bundles as $bundle_key => $bundle) {

      $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type, $bundle_key);

      foreach ($field_definitions as $field_key => $field_value) {

        $field_token_key = strtolower($field_definitions[$field_key]->getName());
        $field_token_key = str_replace(' ','_',$field_token_key);
            
        if ($field_definitions[$field_key]->isRequired() && !in_array($field_key, $forbidden_fields)) {
          if ($entity_type == 'profile') {
            $available_fields['[user:' . $bundle_key . ':' . $field_token_key . ']'] = '[user:' . $bundle_key . ':' . $field_token_key . ']';
          }
          $available_fields['[' . $entity_type . ':' . $field_token_key . ']'] = '[' . $entity_type . ':' . $field_token_key . ']'; 
        }
            
      }

    }

    return $available_fields;

  }

  protected function getBundle($entity_type) {

    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($entity_type);
    return $bundles;    

  }


}
