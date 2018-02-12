<?php

/**
 * This file declares managed database records which will be automatically
 * inserted, updated, or deleted from the database as appropriate per extension
 * lifecycle events. For more details, see "hook_civicrm_managed" (at
 * https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_managed/) as well
 * as "API and the Art of Installation" (at
 * https://civicrm.org/blogs/totten/api-and-art-installation).
 */
require_once 'memrel.civix.php';
use CRM_Memrel_ExtensionUtil as E;

return array(
  array(
    'module' => E::LONG_NAME,
    'name' => 'Membership by Relationship - Conferment Relationship Type',
    'entity' => 'RelationshipType',
    'params' => array(
      'name_a_b' => 'membership_conferment',
      'name_b_a' => 'membership_conferment',
      'label_a_b' => E::ts('Membership Conferment'),
      'label_b_a' => E::ts('Membership Conferment'),
      'is_reserved' => 1,
      'is_active' => 1,
      'description' => E::ts('Used by the Membership by Relationship (org.chorusamerica.memrel) extension.'),
    ),
  ),
);
