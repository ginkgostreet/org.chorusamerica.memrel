<?php

class CRM_MemrelTest extends \PHPUnit_Framework_TestCase {

  /**
   * Helper function to create test data.
   *
   * @return array
   *   Contains IDs for two newly created contact records.
   */
  protected function createContacts() {
    $contactA = civicrm_api3('Contact', 'create', array(
      'contact_type' => 'Individual',
      'first_name' => 'A',
    ));
    $contactB = civicrm_api3('Contact', 'create', array(
      'contact_type' => 'Individual',
      'first_name' => 'B',
    ));

    return array($contactA['id'], $contactB['id']);
  }

  /**
   * Helper function which creates relationships without triggering CiviCRM's
   * post hook.
   *
   * Avoiding triggering the post hook is advantageous because it allows direct
   * testing of code even if the post hook delegates to that code. In other
   * words, it prevents our custom extension code from getting executed during
   * mocking.
   *
   * Caveat: This method assumes it will be provided parameters which will
   * result in the creation of a unique relationship.
   *
   * @param array $params
   *   Values keyed by column names.
   * @return array
   *   Created relationship in the format of api.Relationship.getsingle.
   */
  protected function createRelationship(array $params) {
    // Standardize value to int and default to active
    $params['is_active'] = CRM_Utils_Array::value('is_active', $params, 1) ? 1 : 0;

    $rel = new CRM_Contact_DAO_Relationship();
    $rel->copyValues($params);
    CRM_Core_DAO::executeQuery(CRM_Utils_SQL_Insert::dao($rel)->toSQL());

    return civicrm_api3('Relationship', 'getsingle', $params);
  }

}
