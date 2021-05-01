<?php

namespace Drupal\migration_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Contains main function for settings.
 */
class MigrationModuleURLSettingsPage extends FormBase {

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
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
    ];
    return $form;
  }

   /**
   * After Import button is clicked.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $response = -1;
    $messenger = \Drupal::messenger();
    if ($response >= 0) {
      $messenger->addStatus($this->t('@response nodes has been imported.', ['@response' => $values["migration_module_json_url"]]));
    }
    else {
      $messenger->addError($this->t('Error @response: Unable to import. Please check format of your JSON file.', ['@response' => $values["migration_module_json_url"]]));
    }
  }

}
