<?php

namespace Drupal\migration_module\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Render\Element;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Contains main function for settings.
 */
class MigrationModuleURLSettingsPage extends FormBase {

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Constructs a new QueryInterface class.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $entityQuery
   *   The module handler.
   */
  public function __construct(QueryFactory $entityQuery) {
    $this->entityQuery = $entityQuery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query')
    );
  }

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
      -3 => "No data found",
    ];
    $response = $this->importJsonData();
    $message = $this->messenger();
    if ($response >= 0) {
      $message->addStatus($this->t('@response nodes has been imported.',
       ['@response' => $response]));
    }
    elseif ($response == -99) {
      $message->addWarning($this->t('Warning:: Different Number of
      Company and Users Imported. Please check Drupal and JOSN and make sure
      everything is place.'));
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
    elseif (!UrlHelper::isValid($import_url, TRUE)) {
      return -2;
    }
    else {
      $data = Json::decode(file_get_contents($import_url));
      if (empty($data)) {
        return -3;
      }
      $count_user = 0;
      $count_company = 0;
      foreach ($data as $item) {
        $result1 = 0;
        $result2 = 0;
        $connection = $this->entityQuery;
        $ids = $this->entityQuery->get('node')
          ->condition('type', 'user_import')
          ->condition('field_id', $item['id'], "=")
          ->execute();

        if (empty($ids)) {
          $node = Node::create([
            'type'        => 'user_import',
            'field_id'       => $item['id'],
            'title'       => $item['name'],
            'field_username'       => $item['username'],
            'field_email'       => $item['email'],
            'field_address_street'       => $item['address']['street'],
            'field_address_suite'       => $item['address']['suite'],
            'field_address_city'       => $item['address']['city'],
            'field_address_zipcode'       => $item['address']['zipcode'],
            'field_address_geo_lat'       => $item['address']['geo']['lat'],
            'field_address_geo_lng'       => $item['address']['geo']['lng'],
            'field_phone'       => $item['phone'],
            'field_website'       => $item['website'],
          ]);
          $result1 = $node->save();
          if ($result1 != 0) {
            $count_user++;
          }
        }
        $ids = $this->entityQuery->get('node')
          ->condition('type', 'company_import')
          ->condition('field_id_company', $item['id'], "=")
          ->execute();
        if (empty($ids)) {
          $node = Node::create([
            'type' => 'company_import',
            'field_id_company' => $item['id'],
            'title' => $item['company']['name'],
            'field_catchphrase' => $item['company']['catchPhrase'],
            'field_bs' => $item['company']['bs'],
          ]);
          $result2 = $node->save();
          if ($result2 != 0) {
            $count_company++;
          }
        }
      }
    }
    if ($count_user == $count_company) {
      return $count_company;
    }
    else {
      return -99;
    }
  }

}
