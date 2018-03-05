<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed/
return array (
  0 =>
  array (
    'name' => 'Cron:MemRelSync.createqueue',
    'entity' => 'Job',
    'params' =>
    array (
      'version' => 3,
      'name' => 'Membership by Relationship: Populate queue',
      'description' => 'Prepares a queue of relationships to be evaluated later
        for membership conferment. Recommended for CLI only; see README.md.',
      'run_frequency' => 'Yearly',
      'api_entity' => 'MemRelSync',
      'api_action' => 'createqueue',
      'is_active' => 0,
      'parameters' => 'rel_type_id = [3,5,7,etc.]',
    ),
  ),
);
