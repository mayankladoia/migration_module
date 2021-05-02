<?php

/**
 * @file
 * Implement Drush Command to import data.
 */

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\UrlHelper;
use Drupal\node\Entity\Node;

/**
 * @file
 * Drush integration for the Migration module.
 */

/**
 * Implements hook_drush_command().
 */
function migration_module_drush_command() {
  $items = [];
  $items['import-json'] = [
    'description' => 'Import data from JSON URL',
    'drupal dependencies' => ['migration_module'],
    'aliases' => ['import:json'],
  ];
  return $items;
}

/**
 * Callback function for drush import-json command.
 */
function drush_migration_module_import_json() {
  $import_url = Drupal::config('migration_module.settings')->get('migration_module_json_url');
  $response = 0;
  $errors = [
    -1 => "No Import URL",
    -2 => "Invalid URL",
    -3 => "No data found",
  ];
  if ($import_url == "") {
    $response = -1;
    drush_print(t('Error:: @response.
      Check your migration_module config.',
      ['@response' => $errors[$response]]));
    return;
  }
  elseif (!UrlHelper::isValid($import_url, TRUE)) {
    $response = -2;
    drush_print(t('Error:: @response. Unable to import.
    Please check your JSON feed URL.',
      ['@response' => $errors[$response]]));
    return;
  }
  else {
    $req = Drupal::httpClient()->get($import_url, array('headers' => array('Accept' => 'text/plain')));
    $data = Json::decode((string) $req->getBody());
    if (empty($data)) {
      $response = -3;
      drush_print(t('Error:: @response. Unable to import.
      Please check your JSON feed URL.',
        ['@response' => $errors[$response]]));
      return;
    }
    $count_user = 0;
    $count_company = 0;
    foreach ($data as $item) {
      $ids = Drupal::entityQuery('node')
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
      $ids = Drupal::entityQuery('node')
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
    drush_print(t('@response nodes has been imported.
    @user - Users & @company - Company',
      [
        '@response' => $count_user + $count_company,
        '@user' => $count_user,
        '@company' => $count_company,
      ]));
    return;
  }
  else {
    drush_print(t('Warning:: Different Number of
      Company and Users Imported. Please check Drupal and JOSN and make sure
      everything is place.'));
    return;
  }

}
