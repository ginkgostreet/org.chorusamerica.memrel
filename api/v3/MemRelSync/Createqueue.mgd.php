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
      'name' => 'Populate queue of contact pairs to evaluate for shadow relationships',
      'description' => 'A memory-intensive process that need occur only on extension install or reconfigure',
      'run_frequency' => 'Yearly',
      'api_entity' => 'MemRelSync',
      'api_action' => 'createqueue',
      'is_active' => 0,
      'parameters' => '',
    ),
  ),
);
