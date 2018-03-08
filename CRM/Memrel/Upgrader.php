<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Memrel_Upgrader extends CRM_Memrel_Upgrader_Base {

  public function install() {
    $this->reconfigureMembershipConferment();
  }

  protected function reconfigureMembershipConferment() {
    $membershipTypeIds = array(
      8, // Chorus Member (budgets up to $87,499)
      9, // Chorus Member (budget of $1 million+)
      10, // Chorus Member (budget of $87,500 - $999,999)
    );

    foreach ($membershipTypeIds as $id) {
      civicrm_api3('MembershipType', 'create', array(
        'id' => $id,
        'relationship_type_id' => array(
          4, // Employer Of
          11, // Has an Administrative staff member
          12, // Has a Board member
          13, // Has as Chief Administrative Director
          14, // Has as Chief Artistic Leader
          15, // Has as Board President/Chairman
          19, // Has an Artistic staff member
          32, // Has a Primary Contact
        ),
        'relationship_direction' => array(
          'b_a', // Employer Of
          'b_a', // Has an Administrative staff member
          'b_a', // Has a Board member
          'b_a', // Has as Chief Administrative Director
          'b_a', // Has as Chief Artistic Leader
          'b_a', // Has as Board President/Chairman
          'b_a', // Has an Artistic staff member
          'b_a', // Has a Primary Contact
        ),
      ));
    }

    civicrm_api3('MembershipType', 'create', array(
      'id' => 11, // Library Subscription
      'relationship_type_id' => array(
        36, // Has a Library Guest User (IP access) of
      ),
      'relationship_direction' => array(
        'b_a', // Has a Library Guest User (IP access) of
      ),
    ));
  }

}
