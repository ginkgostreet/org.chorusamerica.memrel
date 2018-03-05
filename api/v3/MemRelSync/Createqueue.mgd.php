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
        for membership conferment. Because it is expected to be a
        memory-intensive process, it is recommended for use on the command line
        interface only. This API should not need to be used except when
        membership type configuration is changed in a way not currently
        supported by CiviCRM (e.g., after installation of this extension).',
      'run_frequency' => 'Yearly',
      'api_entity' => 'MemRelSync',
      'api_action' => 'createqueue',
      'is_active' => 0,
      'parameters' => '',
    ),
  ),
);
