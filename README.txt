Readme
================================================================================

CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration

INTRODUCTION
================================================================================
This module imports from the JSON endpoint into Drupal as nodes of each
content type


REQUIREMENTS
================================================================================
Drupal 8.x


INSTALLATION
================================================================================
Steps:
1. Add migration_module_content_type to you custom module folder
2. Import content type from migration_module_content_type by installing the
   module
3. Add migration_module to you custom module folder
4. Install migration_module


CONFIGURATION
================================================================================
1. Enter URL of JSON import in 'Enter JSON URL for import' textfield
2. Click import to import User and Company as node
Alternate method to import data using drush command
drush import-json
