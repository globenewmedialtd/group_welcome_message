<?php

namespace Drupal\group_welcome_message\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Group Welcome Message settings.
 */
class GroupWelcomeMessageSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'group_welcome_message_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'group_welcome_message.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $settings = $this->config('group_welcome_message.settings');

    $subject_label = $settings->get('subject_label');
    $body_label = $settings->get('body_label');
    $body_existing_label = $settings->get('body_existing_label');


    $available_filters = filter_formats();

    foreach($available_filters as $id => $filter) {
      $filter_options[$id] = $filter->label();
    }  


    $form['subject_label'] = [
      '#type'          => 'textfield',
      '#default_value' => !empty($subject_label) ? $subject_label : 'Subject',
      '#description' => $this->t('Define your own label for the subject field')
    ];

    $form['body_label'] = [
      '#type'          => 'textfield',
      '#default_value' => !empty($body_label) ? $body_label : 'Body for new users',
      '#description' => $this->t('Define your own label for the body for new users field')
    ];

    $form['body_existing_label'] = [
      '#type'          => 'textfield',
      '#default_value' => !empty($body_existing_label) ? $body_existing_label : 'Body for existing users',
      '#description' => $this->t('Define your own label for the body for existing users field')
    ];

    $form['selected_format'] = [
      '#type'          => 'select',
      '#options'       => $filter_options,
      '#default_value' => $settings->get('selected_format'),
    ];

    $form['show_token_info'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Show token info'),
      '#default_value' => $settings->get('show_token_info'),
      '#description' => $this->t('If enabled a bok with available tokens are shown to users.')
    ];
    

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $this->configFactory->getEditable('group_welcome_message.settings');

    // Save configurations.
    $settings->set('subject_label', $form_state->getValue('subject_label'))->save();
    $settings->set('body_label', $form_state->getValue('body_label'))->save();
    $settings->set('body_existing_label', $form_state->getValue('body_existing_label'))->save();
    $settings->set('selected_format', $form_state->getValue('selected_format'))->save();
    $settings->set('show_token_info', $form_state->getValue('show_token_info'))->save();

  }

}

