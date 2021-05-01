<?php

namespace Drupal\migration_module\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Contains main function for settings.
 */
class MigrationModuleURLSettingsPage extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'migration_module_settings_page';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['migration_module.settings'];
  }

  /**
   * Building form to save JSON URL.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('migration_module.settings');
    $form['migration_module_json_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter JSON URL for import'),
      '#required' => TRUE,
      '#default_value' => $config->get('migration_module_json_url'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('migration_module.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

}
