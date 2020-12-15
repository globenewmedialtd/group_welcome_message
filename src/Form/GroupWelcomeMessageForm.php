<?php

namespace Drupal\group_welcome_message\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\GroupStorageInterface;
use Drupal\Core\Routing;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;

/**
 * Class GroupWelcomeMessageForm.
 */
class GroupWelcomeMessageForm extends EntityForm {



  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $group_welcome_message = $this->entity;

    // Make the label group id to avoid dupication
    // Set the entity reference field and attach given group_id

    // Get the group id
    $group_id = \Drupal::routeMatch()->getParameter('group');

    // Get settings
    $settings = $this->config('group_welcome_message.settings');


    if ($this->operation == 'add') {

      $group_welcome_message->setGroup($group_id);
      $group_storage = \Drupal::entityTypeManager()->getStorage('group');
      $group = $group_storage->load($group_welcome_message->getGroup());

      $label_default_value = $this->t('Welcome Message for') . ' ' .
                             $group->id() . '-' . $group->label();
      $group_welcome_message->set('label', $label_default_value);

    }

    // Change page title for the edit operation
    if ($this->operation == 'edit') {
      // Get the group id
      $group_storage = \Drupal::entityTypeManager()->getStorage('group');
      $group = $group_storage->load($group_welcome_message->getGroup());

    }

    $form['#attached']['library'][] = 'group_welcome_message/design';


    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $group_welcome_message->label(),
      '#required' => TRUE,
      '#attributes' => ['class' => ['hidden']],
      '#disabled' => TRUE,
      '#title_display' => 'invisible'
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $group_welcome_message->id(),
      '#machine_name' => [
        'exists' => '\Drupal\group_welcome_message\Entity\GroupWelcomeMessage::load',
      ],
      // Hide the machine name
      '#disabled' => !$group_welcome_message->isNew(),
    ];


    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $settings->get('subject_label'),
      '#maxlength' => 255,
      '#default_value' => $group_welcome_message->getSubject(),
      '#required' => TRUE,
    ];

 
    $form['body'] = [
      '#type' => 'text_format',
      '#title' => $settings->get('body_label'),
      '#default_value' => $group_welcome_message->getBody()['value'],
      '#required' => TRUE,
      '#format' => $settings->get('selected_format'),
      '#allowed_formats' => [
        $settings->get('selected_format')
      ]
    ];

    //$group_welcome_message->getBody()['format']

    $form['body_existing'] = [
      '#type' => 'text_format',
      '#title' => $settings->get('body_existing_label'),
      '#default_value' => $group_welcome_message->getBodyExisting()['value'],
      '#required' => FALSE,
      '#format' => $settings->get('selected_format'),
      '#allowed_formats' => [
        $settings->get('selected_format')
      ]
    ];

    //$group_welcome_message->getBodyExisting()['format'],

    $form['group'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'group',
      '#default_value' => $group,
      '#title' => $this->t('Group'),
      '#disabled' => TRUE
    ];

    $form['available_tokens'] = array(
      '#type' => 'details',
      '#title' => t('Available Tokens'),
      '#open' => FALSE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    );

    $suppported_tokens = array('site','user','group');

    $options = [
      'show_restricted' => TRUE,
      'show_nested' => TRUE,
      'global_types' => FALSE,
      'whitelist' =>
        [
          '[user:mail]',
          '[user:one-time-login-url]',
          '[user:display-name]',
          '[group:title]',
          '[site:name]'
        ]
    ];

    $form['available_tokens']['#access'] = $settings->get('show_token_info');


    $form['available_tokens']['tokens'] = \Drupal::service('group_welcome_message.tree_builder')
      ->buildRenderable($suppported_tokens,$options);



    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $group_welcome_message = $this->entity;

    $status = $group_welcome_message->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Welcome Message.', [
          '%label' => $group_welcome_message->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Welcome Message.', [
          '%label' => $group_welcome_message->label(),
        ]));
    }


    if ($status != SAVED_NEW) {      

      $url = Url::fromRoute('view.group_members.page_1',['group' => $group_welcome_message->getGroup()]);
      $form_state->setRedirectUrl($url);

    }




  }

}
