<?php

namespace Drupal\migration_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class to display the imported data.
 */
class MigrationModuleURLResultPage extends ControllerBase {

  /**
   * The storage handler class for nodes.
   *
   * @var \Drupal\node\NodeStorage
   */
  private $nodeStorage;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity
   *   The Entity type manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityQuery type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('entity_type.manager'),
    $container->get('entity.query')
    );
  }

  /**
   * Format imported data in html table format.
   */
  public function displayText(): array {
    $output = [];
    $result = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'user_import')
      ->execute();
    $html = '<table style="table-layout:fixed;">
<th style="width: 5%">id</th>
<th style="width: 10%">name</th>
<th style="width: 10%">username</th>
<th class="email" style="width: 10%">email</th>
<th style="width: 10%">address</th>
<th style="width: 10%">geo</th>
<th style="width: 10%">phone</th>
<th style="width: 10%">website</th>
<th class="company" style="width: 25%">company</th>';
    foreach ($result as $nid) {
      $data = $this->nodeStorage->load($nid);
      $html .= "<tr>";
      $html .= '<td>' . $data->get('field_id')->value . "</td>";
      $html .= '<td>' . $data->get('title')->value . "</td>";
      $html .= '<td>' . $data->get('field_username')->value . "</td>";
      $html .= '<td class="email" >' . $data->get('field_email')->value . "</td>";
      $html .= '<td>' . $data->get('field_address_street')->value
      . "<br />" . $data->get('field_address_suite')->value
      . "<br />" . $data->get('field_address_city')->value
      . "<br />" . $data->get('field_address_zipcode')->value
      . "</td>";
      $html .= '<td>Lat:' . $data->get('field_address_geo_lat')->value
      . "<br />Lng:" . $data->get('field_address_geo_lng')->value
      . "</td>";
      $html .= '<td>' . $data->get('field_phone')->value . "</td>";
      $html .= '<td>' . $data->get('field_website')->value . "</td>";
      $company = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', 'company_import')
        ->condition('field_id_company', $data->get('field_id')->value, "=")
        ->execute();
      foreach ($company as $nid_company) {
        $data_company = $this->nodeStorage->load($nid_company);
        $html .= '<td class="company">' . $data_company->get('title')->value
        . "<br />" . $data_company->get('field_catchphrase')->value
        . "<br />" . $data_company->get('field_bs')->value
        . "</td>";
      }
      $html .= "<tr>";
    }
    $html .= "</table>";
    $output['hello'] = [
      '#title' => 'JSON Import',
      '#markup' => $html,
    ];
    return $output;
  }

}
