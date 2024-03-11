<?php
/*
 * factory to retrieve the correct adress parser
*/
class AddressParserFactory {
    public static function createParser($countryCode) {
    $className = ucfirst(strtolower($countryCode)) . 'AddressParser';
    //TODO check if this works in plain civicrm.....
    $filePath = __DIR__ . "{$className}.php";

    if (file_exists($filePath)) {
    require_once $filePath;
    if (class_exists($className)) {
       return new $className();
     } else {
require_once("DefaultAddressParser.php");
                return new DefaultAddressParser();
            }
        } else {
require_once("DefaultAddressParser.php");
                return new DefaultAddressParser();
        }

    }
}

?>
