<?php

require_once 'memrel.civix.php';

use CRM_Memrel_ExtensionUtil as E;

return array(
  'memrel_mapping' => array(
    'group_name' => 'Global Settings for Membership by Relationship',
    'group' => E::LONG_NAME,
    'name' => 'memrel_mapping',
    'type' => 'Array',
    // format: "shadow" relationship type ID => array(relTypeIDs which should
    // result in creation of "shadow")
    'default' => array(),
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => 'Membership-Conferring Relationship Types',
    'description' => 'Selected relationship types will be "shadowed" by a membership conferment relationship which controls membership benefits.',
  ),

);
