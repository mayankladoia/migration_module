<?php

namespace Drupal\migration_module\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Render\Element;

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
    $config_edit = $this->configFactory()->getEditable('migration_module.settings');
    foreach (Element::children($form) as $variable) {
      $config_edit->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config_edit->save();
    $values = $form_state->getValues();
    $errors = [
      -1 => "No Import URL",
      -2 => "Invalid URL",
      -3 => "error type 3",
    ];
    $response = $this->importJsonData();
    $message = $this->messenger();
    if ($response >= 0) {
      $message->addStatus($this->t('@response nodes has been imported.',
       ['@response' => $response]));
    }
    else {
      $message->addError($this->t('Error:: @response. Unable to import.
      Please check format of your JSON file.',
       ['@response' => $errors[$response]]));
    }
  }

  /**
   * Main Function to import data.
   */
  public function importJsonData() {
    $import_url = $this->config('migration_module.settings')
      ->get('migration_module_json_url');
    if ($import_url == "") {
      return -1;
    }
    elseif (!UrlHelper::isValid ($import_url, TRUE)) {
      return  -2;
    }
    return 0;
  }

}
