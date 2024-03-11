<?php
/*
   *
   * generic adress parser class
   */
interface AddressParser {
    public function parseAddress($record);
    public function parseStreetAddress($streetAddress);
}

?>
