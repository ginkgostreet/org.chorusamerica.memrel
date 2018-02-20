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

}
