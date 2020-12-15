<?php

namespace Drupal\group_welcome_message\Plugin\views\relationship;

use Drupal\views\Views;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\relationship\RelationshipPluginBase;

/**
 * Views relationship plugin for datasources.
 *
 * @ingroup views_relationship_handlers
 *
 * @ViewsRelationship("group_welcome_message")
 */
class GroupWelcomeMessageRelationship extends RelationshipPluginBase {

  
  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();    
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }

    /**
   * Overrides \Drupal\views\Plugin\views\HandlerBase::init().
   *
   * Init handler to let relationships live on tables other than
   * the table they operate on.
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    //kint($view->id());
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Figure out what base table this relationship brings to the party.

    $argument = $this->view->args;

    
    $table_data = Views::viewsData()
      ->get($this->definition['base']);
    $base_field = empty($this->definition['base field']) ? $table_data['table']['base']['field'] : $this->definition['base field'];
    $this
      ->ensureMyTable();
    $def = $this->definition;
    $def['table'] = $this->definition['base'];
    $def['field'] = $base_field;
    $def['left_table'] = $this->tableAlias;
    $def['left_field'] = $this->realField;
    $def['adjusted'] = TRUE;
    if (!empty($this->options['required'])) {
      $def['type'] = 'INNER';
    }
    if (!empty($this->definition['extra'])) {
      $def['extra'] = $this->definition['extra'];
    }
    if (!empty($def['join_id'])) {
      $id = $def['join_id'];
    }
    else {
      $id = 'standard';
    }

    if (isset($argument) && isset($argument[0])) {

      $custom_extra = [
        [
          'field' => 'group',
          'value' => $argument[0]
        ]
      ];
    }

    $def['extra'] = $custom_extra;   
    

    $join = Views::pluginManager('join')
      ->createInstance($id, $def);

    // use a short alias for this:
    $alias = $def['table'] . '_' . $this->table;
    $this->alias = $this->query
      ->addRelationship($alias, $join, $this->definition['base'], $this->relationship);

    // Add access tags if the base table provide it.
    if (empty($this->query->options['disable_sql_rewrite']) && isset($table_data['table']['base']['access query tag'])) {
      $access_tag = $table_data['table']['base']['access query tag'];
      $this->query
        ->addTag($access_tag);
    }
  }



}
