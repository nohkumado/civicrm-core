<?php
require_once("AddressParser.php");

/*
   * placeholder for the default civicrm adress parser
*/
class DefaultAdressParser implements AdressParser
{
  public function parseAddress($record) {
    //TODO place holder
    //$result['street_name'] = $this->parseStreetAddress($record['street_name']);
    //finally merge all parse values
    //if (!empty($allParseValues)) {
    //  $contactValues += $allParseValues;
    //}
  }
  public function parseStreetAddress($streetAddress) {

    $emptyParseFields = $parseFields = [
      'street_name' => '',
      'street_unit' => '',
      'street_number' => '',
      'street_number_suffix' => '',
    ];

    if (empty($streetAddress)) {
      return $parseFields;
    }

    $streetAddress = trim($streetAddress);

    $matches = [];
    print("wrong locale '".print_r($locale,true)."' vs fr_FR'<br>\n");
    return Array();
  }
}
?>
