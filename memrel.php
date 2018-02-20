<?php

require_once 'memrel.civix.php';
use CRM_Memrel_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function memrel_civicrm_config(&$config) {
  _memrel_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function memrel_civicrm_xmlMenu(&$files) {
  _memrel_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function memrel_civicrm_install() {
  _memrel_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * After the membership_conferment relationship is installed, update all
 * membership types currently using "primary contact" for conferment to this.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function memrel_civicrm_postInstall() {
  _memrel_civix_civicrm_postInstall();

  $primaryContactRelTypeId = 32;
  $confermentRelTypeId = CRM_Memrel_Conferment::getDefaultConfermentRelTypeId();

  civicrm_api3('MembershipType', 'get', array(
    'relationship_type_id' => $primaryContactRelTypeId,
    'api.MembershipType.create' => array(
      'relationship_type_id' => $confermentRelTypeId,
    ),
  ));
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function memrel_civicrm_uninstall() {
  _memrel_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function memrel_civicrm_enable() {
  _memrel_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function memrel_civicrm_disable() {
  _memrel_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function memrel_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _memrel_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function memrel_civicrm_managed(&$entities) {
  _memrel_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function memrel_civicrm_caseTypes(&$caseTypes) {
  _memrel_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function memrel_civicrm_angularModules(&$angularModules) {
  _memrel_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function memrel_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _memrel_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu/
 */
function memrel_civicrm_navigationMenu(&$menu) {
  _memrel_civix_insert_navigation_menu($menu, 'Administer/CiviMember', array(
    'label' => E::ts('Membership by Relationship'),
    'name' => 'memrel',
    'url' => 'civicrm/admin/member/memrel',
    'permission' => 'administer CiviCRM',
    'separator' => 1,
  ));
  _memrel_civix_navigationMenu($menu);
}

/**
 * Implements hook_civicrm_post().
 *
 * Delegates to CRM_Memrel_Conferment (if appropriate) for the management of
 * "shadow" membership-conferment relationships.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_post/
 */
function memrel_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  // This extension is concerned with Relationships only.
  if ($objectName !== 'Relationship') {
    return;
  }

  /* @var $objectRef CRM_Contact_DAO_Relationship */
  try {
    CRM_Memrel_Utils::makeRelationshipUsable($objectRef);

    // Bail out if the relationship is not related to membership conferment.
    $shadowRelTypeIds = CRM_Memrel_Conferment::getConfermentRelTypeIds($objectRef->relationship_type_id);
    if (empty($shadowRelTypeIds)) {
      return;
    }

    CRM_Memrel_Conferment::doSync($objectRef->contact_id_a, $objectRef->contact_id_b);
  }
  // Catch unusable relationship (and possibly other?) errors.
  catch (CRM_Core_Exception $e) {
    Civi::log()->error($e->getMessage(), $e->getErrorData());
  }
  // Catch API errors that might be thrown by makeRelationshipUsable() or doSync().
  catch (CiviCRM_API3_Exception $e) {
    Civi::log()->error($e->getMessage(), $e->getExtraParams());
  }
}
