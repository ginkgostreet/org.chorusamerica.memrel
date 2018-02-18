<?php

use CRM_Memrel_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Memrel_Form_Settings extends CRM_Core_Form {

  private $shadowRelationshipTypeId;

  public function setDefaultValues() {
    return array(
      'memrel_mapping' => Civi::settings()->get('memrel_mapping'),
    );
  }

  public function buildQuickForm() {
    // in case the need arises to do multiple mappings, the fields will be keyed
    // by the ID of the "shadow" relationship type
    $fieldName = 'memrel_mapping[' . $this->getShadowRelationshipTypeId() . ']';
    $this->addEntityRef($fieldName, E::ts('Membership-Conferring Relationship Types'), array(
        'entity' => 'RelationshipType',
        'api' => array(
          // avoid loop -- don't allow selection of the "shadow" relationship itself
          'params' => array(
            'name_a_b' => array('!=' => 'membership_conferment'),
            'name_b_a' => array('!=' => "membership_conferment"),
          ),
        ),
        'multiple' => TRUE,
        'select' => array('minimumInputLength' => 0,),
      ), TRUE
    );

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    // the entityRef/select2 widget (at least in the current version) gives us
    // a comma-separated string
    foreach ($values['memrel_mapping'] as &$selections) {
      if (!is_array($selections)) {
        $selections = explode(',', $selections);
      }
    }

    Civi::settings()->set('memrel_mapping', $values['memrel_mapping']);
    CRM_Core_Session::setStatus(E::ts('Saved'), NULL, 'success');

    parent::postProcess();
  }

  /**
   * Returns the ID of the "shadow" relationship type that the user selections
   * will be mapped to.
   *
   * @return int
   */
  protected function getShadowRelationshipTypeId() {

    if (!isset($this->shadowRelationshipTypeId)) {
      $this->shadowRelationshipTypeId = CRM_Memrel_Utils::getDefaultConfermentRelTypeId();
    }

    return $this->shadowRelationshipTypeId;
  }

}
