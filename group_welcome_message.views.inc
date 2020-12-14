<?php

/**
 * Implements hook_views_data_alter().
 */
function group_welcome_message_views_data_alter(array &$data) {


  $data['users_field_data']['group_welcome_message_logger'] = [
  'relationship' => [
    //Display name in View Relationship section
    'title' => 'Group Welcome Message relationship',
    'help' => 'Join User and Group Welcome Message Logger entities by user',
    //the name of the table
    'base' => 'group_welcome_message_logger',
    //Field in users_field_data table
    'base field' => 'user_id',
    //`name` field in Player table
    'relationship field' => 'uid',
    'field' => 'uid',
    'id' => 'group_welcome_message'
  ]
];

}


