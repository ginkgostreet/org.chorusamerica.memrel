<?php

class CRM_MemrelTest extends \PHPUnit_Framework_TestCase {

  /**
   * Relationship type IDs of new relationship types for testing, keyed by names.
   *
   * @var array
   */
  private $relTypeIds = array();

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->callback(function() {
        $admin = civicrm_api3('RelationshipType', 'create', array(
          'name_a_b' => 'test_admin',
          'name_b_a' => 'test_admin',
        ));

        $exec = civicrm_api3('RelationshipType', 'create', array(
          'name_a_b' => 'test_exec',
          'name_b_a' => 'test_exec',
        ));

        Civi::settings()->set('memrel_mapping', array(
          CRM_Memrel_Conferment::getDefaultConfermentRelTypeId() => array($admin['id'], $exec['id']),
        ));
      }, 'configureRelationships')
      ->apply();
  }

  /**
   * Helper function for retrieving relationship type IDs by name.
   *
   * Assumes the same name is used for both directions of the relationship.
   *
   * @param string $name
   *   The relationship type name.
   * @return string
   *   The relationship type ID.
   */
  protected function getRelTypeId($name) {
    if (!isset($this->relTypeIds[$name])) {
      $this->relTypeIds[$name] = civicrm_api3('RelationshipType', 'getvalue', array(
        'return' => 'id',
        'name_a_b' => $name,
        'name_b_a' => $name,
      ));
    }
    return $this->relTypeIds[$name];
  }

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
