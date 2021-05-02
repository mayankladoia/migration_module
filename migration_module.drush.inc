<?php

/**
 * @file
 * Implement Drush Command to import data.
 */

use Drupal\migration_module\Form\MigrationModuleURLSettingsPage;

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
  $m = new MigrationModuleURLSettingsPage();
  $errors = [
    -1 => "No Import URL",
    -2 => "Invalid URL",
    -3 => "No data found",
  ];
  $response = $m->importJsonData();
  if ($response >= 0) {
    drush_print(t('@response nodes has been imported.',
       ['@response' => $response]));
  }
  elseif ($response == -99) {
    drush_print(t('Warning:: Different Number of
      Company and Users Imported. Please check Drupal and JOSN and make sure
      everything is place.'));
  }
  else {
    drush_print(t('Error:: @response. Unable to import.
      Please check format of your JSON file.',
     ['@response' => $errors[$response]]));
  }
}
