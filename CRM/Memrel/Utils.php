<?php

/**
 * An assortment of generic utilities needed by this extension.
 */
class CRM_Memrel_Utils {

  /**
   * Ensures the passed relationship has contact_id_a, contact_id_b, and
   * relationship_type_id properties, fetching them from the DB if necessary.
   *
   * @param CRM_Contact_DAO_Relationship $rel
   *   A relationship object.
   */
  public static function makeRelationshipUsable(CRM_Contact_DAO_Relationship $rel) {

  }

}
