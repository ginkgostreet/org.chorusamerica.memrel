<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed/
return array (
  0 => 
  array (
    'name' => 'Cron:MemRelSync.processqueue',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Membership by Relationship: Process queue',
      'description' => 'Triggers the creation of indirect memberships where
        conferment relationships exist.',
      'run_frequency' => 'Always',
      'api_entity' => 'MemRelSync',
      'api_action' => 'processqueue',
      'is_active' => 0,
      'parameters' => 'max_run_time = [# of seconds to let the job run; optional - defaults to 30]',
    ),
  ),
);
