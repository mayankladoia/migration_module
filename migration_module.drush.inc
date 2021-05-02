<?php

use Drupal\migration_module\Form\MigrationModuleURLSettingsPage;

/**
 * @file
 * Drush integration for the Token module.
 */

/**
* Implements hook_drush_command().
*/
function migration_module_drush_command() {
 $items = array();
 $items['import-json'] = [
    'description' => 'Import data from JSON URL',
    'drupal dependencies' => ['migration_module'],
    'aliases' => ['import:json'],
 ];
 $items['say-hello'] = [
   'description' => 'Echo the name you type with Say hello command',
   'arguments' => [
     'name' => 'The name for saying hello',
   ],
   'drupal dependencies' => ['migration_module'],
   'aliases' => ['say:hello'],
 ];
 return $items;
}

/**
* Call back function drush_custom_drush_command_say_hello()
* The call back function name in the  following format
*   drush_{module_name}_{item_id_for_command}()
*/
function drush_migration_module_say_hello($name = '') {
 drush_print('Hello ' . $name . ' !');
}
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