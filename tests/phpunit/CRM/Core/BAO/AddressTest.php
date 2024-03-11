<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 * Class CRM_Core_BAO_AddressTest
 * @group headless
 * @group locale
 */
class CRM_Core_BAO_AddressTest extends CiviUnitTestCase {

  use CRMTraits_Custom_CustomDataTrait;

  public function setUp(): void {
    parent::setUp();

    $this->quickCleanup(['civicrm_contact', 'civicrm_address']);
  }

  /**
   * Create() method (create and update modes)
   */
  public function testCreate(): void {
    $contactId = $this->individualCreate();

    $params = [];
    $params['address']['1'] = [
      'street_address' => 'Oberoi Garden',
      'supplemental_address_1' => 'Attn: Accounting',
      'supplemental_address_2' => 'Powai',
      'supplemental_address_3' => 'Somewhere',
      'city' => 'Athens',
      'postal_code' => '01903',
      'state_province_id' => '1000',
      'country_id' => '1228',
      'geo_code_1' => '18.219023',
      'geo_code_2' => '-105.00973',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
    ];

    $params['contact_id'] = $contactId;

    $fixAddress = TRUE;

    CRM_Core_BAO_Address::legacyCreate($params, $fixAddress);
    $addressId = $this->assertDBNotNull('CRM_Core_DAO_Address', 'Oberoi Garden', 'id', 'street_address',
      'Database check for created address.'
    );

    // Now call add() to modify an existing  address

    $params = [];
    $params['address']['1'] = [
      'id' => $addressId,
      'street_address' => '120 Terminal Road',
      'supplemental_address_1' => 'A-wing:3037',
      'supplemental_address_2' => 'Bandra',
      'supplemental_address_3' => 'Somewhere',
      'city' => 'Athens',
      'postal_code' => '01903',
      'state_province_id' => '1000',
      'country_id' => '1228',
      'geo_code_1' => '18.219023',
      'geo_code_2' => '-105.00973',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
    ];
    $params['contact_id'] = $contactId;

    $block = CRM_Core_BAO_Address::legacyCreate($params, $fixAddress);

    $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
      'Database check for updated address by contactId.'
    );
    $this->assertDBNotNull('CRM_Core_DAO_Address', '120 Terminal Road', 'id', 'street_address',
      'Database check for updated address by street_name.'
    );
    $this->contactDelete($contactId);
  }

  /**
   * Add() method ( )
   */
  public function testAdd(): void {
    $contactId = $this->individualCreate();

    $fixParams = [
      'street_address' => 'E 906N Pine Pl W',
      'supplemental_address_1' => 'Editorial Dept',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'El Paso',
      'postal_code' => '88575',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '31.694842',
      'geo_code_2' => '-106.29998',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::fixAddress($fixParams);
    $addAddress = CRM_Core_BAO_Address::writeRecord($fixParams);

    $addParams = $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
      'Database check for created contact address.'
    );

    $this->assertEquals($addAddress->street_address, 'E 906N Pine Pl W', 'In line' . __LINE__);
    $this->assertEquals($addAddress->supplemental_address_1, 'Editorial Dept', 'In line' . __LINE__);
    $this->assertEquals($addAddress->city, 'El Paso', 'In line' . __LINE__);
    $this->assertEquals($addAddress->postal_code, '88575', 'In line' . __LINE__);
    $this->assertEquals($addAddress->geo_code_1, '31.694842', 'In line' . __LINE__);
    $this->assertEquals($addAddress->geo_code_2, '-106.29998', 'In line' . __LINE__);
    $this->assertEquals($addAddress->country_id, '1228', 'In line' . __LINE__);
    $this->contactDelete($contactId);
  }

  /**
   * Add 2 billing addresses using the `CRM_Core_BAO_Address::legacyCreate` mode
   * Only the first array will remain as primary/billing due to the nature of how `legacyCreate` works
   */
  public function testMultipleBillingAddressesLegacymode(): void {
    $contactId = $this->individualCreate();

    $entityBlock = ['contact_id' => $contactId];
    $params = [];
    $params['contact_id'] = $contactId;
    $params['address']['1'] = [
      'street_address' => 'E 906N Pine Pl W',
      'supplemental_address_1' => 'Editorial Dept',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'El Paso',
      'postal_code' => '88575',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '31.694842',
      'geo_code_2' => '-106.29998',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '1',
      'contact_id' => $contactId,
    ];
    $params['address']['2'] = [
      'street_address' => '120 Terminal Road',
      'supplemental_address_1' => 'A-wing:3037',
      'supplemental_address_2' => 'Bandra',
      'supplemental_address_3' => 'Somewhere',
      'city' => 'Athens',
      'postal_code' => '01903',
      'state_province_id' => '1000',
      'country_id' => '1228',
      'geo_code_1' => '18.219023',
      'geo_code_2' => '-105.00973',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '1',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::legacyCreate($params, $fixAddress = TRUE);

    $address = CRM_Core_BAO_Address::getValues($entityBlock);

    $this->assertEquals($address[1]['contact_id'], $contactId);
    $this->assertEquals($address[1]['is_primary'], 1, 'In line ' . __LINE__);
    $this->assertEquals($address[1]['is_billing'], 1, 'In line ' . __LINE__);

    $this->assertEquals($address[2]['contact_id'], $contactId);
    $this->assertEquals($address[2]['is_primary'], 0, 'In line ' . __LINE__);
    $this->assertEquals($address[2]['is_billing'], 0, 'In line ' . __LINE__);

    $this->contactDelete($contactId);
  }

  /**
   * Add() 2 billing addresses, only the last one should be set as billing
   * Using the `CRM_Core_BAO_Address::add` mode
   *
   */
  public function testMultipleBillingAddressesCurrentmode(): void {
    $contactId = $this->individualCreate();

    $entityBlock = ['contact_id' => $contactId];
    $params = [];
    $params['contact_id'] = $contactId;
    $params['address']['1'] = [
      'street_address' => 'E 906N Pine Pl W',
      'supplemental_address_1' => 'Editorial Dept',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'El Paso',
      'postal_code' => '88575',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '31.694842',
      'geo_code_2' => '-106.29998',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '1',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::fixAddress($params['address']['1']);
    CRM_Core_BAO_Address::writeRecord($params['address']['1']);

    // Add address 2
    $params['address']['2'] = [
      'street_address' => '120 Terminal Road',
      'supplemental_address_1' => 'A-wing:3037',
      'supplemental_address_2' => 'Bandra',
      'supplemental_address_3' => 'Somewhere',
      'city' => 'Athens',
      'postal_code' => '01903',
      'state_province_id' => '1000',
      'country_id' => '1228',
      'geo_code_1' => '18.219023',
      'geo_code_2' => '-105.00973',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '1',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::fixAddress($params['address']['2']);
    CRM_Core_BAO_Address::writeRecord($params['address']['2']);

    $addresses = CRM_Core_BAO_Address::getValues($entityBlock);

    // Sort the multidimensional array by id
    usort($addresses, function($a, $b) {
        return $a['id'] <=> $b['id'];
    });

    // Validate both results, remember that the keys have been reset to 0 after usort
    $this->assertEquals($addresses[0]['contact_id'], $contactId);
    $this->assertEquals($addresses[0]['is_primary'], 0, 'In line ' . __LINE__);
    $this->assertEquals($addresses[0]['is_billing'], 0, 'In line ' . __LINE__);

    $this->assertEquals($addresses[1]['contact_id'], $contactId);
    $this->assertEquals($addresses[1]['is_primary'], 1, 'In line ' . __LINE__);
    $this->assertEquals($addresses[1]['is_billing'], 1, 'In line ' . __LINE__);

    $this->contactDelete($contactId);
  }

  /**
   * AllAddress() method ( )
   */
  public function testallAddress(): void {
    $contactId = $this->individualCreate();

    $fixParams = [
      'street_address' => 'E 906N Pine Pl W',
      'supplemental_address_1' => 'Editorial Dept',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'El Paso',
      'postal_code' => '88575',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '31.694842',
      'geo_code_2' => '-106.29998',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::fixAddress($fixParams);
    CRM_Core_BAO_Address::writeRecord($fixParams);

    $addParams = $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
      'Database check for created contact address.'
    );
    $fixParams = [
      'street_address' => 'SW 719B Beech Dr NW',
      'supplemental_address_1' => 'C/o OPDC',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'Neillsville',
      'postal_code' => '54456',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '44.553719',
      'geo_code_2' => '-90.61457',
      'location_type_id' => '2',
      'is_primary' => '',
      'is_billing' => '1',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::fixAddress($fixParams);
    CRM_Core_BAO_Address::writeRecord($fixParams);

    $addParams = $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
      'Database check for created contact address.'
    );

    $allAddress = CRM_Core_BAO_Address::allAddress($contactId);

    $this->assertEquals(count($allAddress), 2, 'Checking number of returned addresses.');

    $this->contactDelete($contactId);
  }

  /**
   * AllAddress() method ( ) with null value
   */
  public function testnullallAddress(): void {
    $contactId = $this->individualCreate();

    $fixParams = [
      'street_address' => 'E 906N Pine Pl W',
      'supplemental_address_1' => 'Editorial Dept',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'El Paso',
      'postal_code' => '88575',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '31.694842',
      'geo_code_2' => '-106.29998',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
      'contact_id' => $contactId,
    ];

    CRM_Core_BAO_Address::fixAddress($fixParams);
    CRM_Core_BAO_Address::writeRecord($fixParams);

    $addParams = $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
      'Database check for created contact address.'
    );

    $contact_Id = NULL;

    $allAddress = CRM_Core_BAO_Address::allAddress($contact_Id);

    $this->assertEquals($allAddress, NULL, 'Checking null for returned addresses.');

    $this->contactDelete($contactId);
  }

  /**
   * GetValues() method (get Address fields)
   */
  public function testGetValues(): void {
    $contactId = $this->individualCreate();

    $params = [];
    $params['address']['1'] = [
      'street_address' => 'Oberoi Garden',
      'supplemental_address_1' => 'Attn: Accounting',
      'supplemental_address_2' => 'Powai',
      'supplemental_address_3' => 'Somewhere',
      'city' => 'Athens',
      'postal_code' => '01903',
      'state_province_id' => '1000',
      'country_id' => '1228',
      'geo_code_1' => '18.219023',
      'geo_code_2' => '-105.00973',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
    ];

    $params['contact_id'] = $contactId;

    $fixAddress = TRUE;

    CRM_Core_BAO_Address::legacyCreate($params, $fixAddress);

    $addressId = $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
      'Database check for created address.'
    );

    $entityBlock = ['contact_id' => $contactId];
    $address = CRM_Core_BAO_Address::getValues($entityBlock);
    $this->assertEquals($address[1]['id'], $addressId);
    $this->assertEquals($address[1]['contact_id'], $contactId);
    $this->assertEquals($address[1]['state_province_abbreviation'], 'AL');
    $this->assertEquals($address[1]['state_province'], 'Alabama');
    $this->assertEquals($address[1]['country'], 'United States');
    $this->assertEquals($address[1]['street_address'], 'Oberoi Garden');
    $this->contactDelete($contactId);
  }

  /**
   * Enable street address parsing.
   *
   * @param string $status
   *
   * @throws \CRM_Core_Exception
   */
  public function setStreetAddressParsing($status) {
    $options = $this->callAPISuccess('Setting', 'getoptions', ['field' => 'address_options'])['values'];
    $address_options = reset($this->callAPISuccess('Setting', 'get', ['return' => 'address_options'])['values'])['address_options'];
    $parsingOption = array_search('Street Address Parsing', $options, TRUE);
    $optionKey = array_search($parsingOption, $address_options, FALSE);
    if ($status && !$optionKey) {
      $address_options[] = $parsingOption;
    }
    if (!$status && $optionKey) {
      unset($address_options[$optionKey]);
    }
    $this->callAPISuccess('Setting', 'create', ['address_options' => $address_options]);
  }

  /**
   * ParseStreetAddress if enabled, otherwise, don't.
   *
   * @throws \CRM_Core_Exception
   */
  public function testParseStreetAddressIfEnabled(): void {
    // Turn off address standardization. Parsing should work without it.
    Civi::settings()->set('address_standardization_provider', NULL);

    // Ensure street parsing happens if enabled.
    $this->setStreetAddressParsing(TRUE);

    $contactId = $this->individualCreate();
    $street_address = '54 Excelsior Ave.';
    $params = [
      'contact_id' => $contactId,
      'street_address' => $street_address,
      'location_type_id' => 1,
    ];

    $result = civicrm_api3('Address', 'create', $params);
    $value = array_pop($result['values']);
    $street_number = $value['street_number'] ?? NULL;
    $this->assertEquals($street_number, '54');

    // Ensure street parsing does not happen if disabled.
    $this->setStreetAddressParsing(FALSE);
    $result = civicrm_api3('Address', 'create', $params);
    $value = array_pop($result['values']);
    $street_number = $value['street_number'] ?? NULL;
    $this->assertEmpty($street_number);

  }

  /**
   * ParseStreetAddress() method (get street address parsed)
   */
  public function testParseStreetAddress(): void {

    // valid Street address to be parsed ( without locale )
    $street_address = "54A Excelsior Ave. Apt 1C";
    $parsedStreetAddress = CRM_Core_BAO_Address::parseStreetAddress($street_address);
    $this->assertEquals($parsedStreetAddress['street_name'], 'Excelsior Ave.');
    $this->assertEquals($parsedStreetAddress['street_unit'], 'Apt 1C');
    $this->assertEquals($parsedStreetAddress['street_number'], '54');
    $this->assertEquals($parsedStreetAddress['street_number_suffix'], 'A');

    // Out-of-range street number to be parsed.
    $street_address = '505050505050 Main St';
    $parsedStreetAddress = CRM_Core_BAO_Address::parseStreetAddress($street_address);
    $this->assertEquals($parsedStreetAddress['street_name'], '');
    $this->assertEquals($parsedStreetAddress['street_unit'], '');
    $this->assertEquals($parsedStreetAddress['street_number'], '');
    $this->assertEquals($parsedStreetAddress['street_number_suffix'], '');

    // valid Street address to be parsed ( $locale = 'en_US' )
    $street_address = "54A Excelsior Ave. Apt 1C";
    $locale = 'en_US';
    $parsedStreetAddress = CRM_Core_BAO_Address::parseStreetAddress($street_address, $locale);
    $this->assertEquals($parsedStreetAddress['street_name'], 'Excelsior Ave.');
    $this->assertEquals($parsedStreetAddress['street_unit'], 'Apt 1C');
    $this->assertEquals($parsedStreetAddress['street_number'], '54');
    $this->assertEquals($parsedStreetAddress['street_number_suffix'], 'A');

    // invalid Street address ( $locale = 'en_US' )
    $street_address = "West St. Apt 1";
    $locale = 'en_US';
    $parsedStreetAddress = CRM_Core_BAO_Address::parseStreetAddress($street_address, $locale);
    $this->assertEquals($parsedStreetAddress['street_name'], 'West St.');
    $this->assertEquals($parsedStreetAddress['street_unit'], 'Apt 1');
    $this->assertNotContains('street_number', $parsedStreetAddress);
    $this->assertNotContains('street_number_suffix', $parsedStreetAddress);

    // valid Street address to be parsed ( $locale = 'fr_CA' )
    $street_address = "2-123CA Main St";
    $locale = 'fr_CA';
    $parsedStreetAddress = CRM_Core_BAO_Address::parseStreetAddress($street_address, $locale);
    $this->assertEquals($parsedStreetAddress['street_name'], 'Main St');
    $this->assertEquals($parsedStreetAddress['street_unit'], '2');
    $this->assertEquals($parsedStreetAddress['street_number'], '123');
    $this->assertEquals($parsedStreetAddress['street_number_suffix'], 'CA');

    // invalid Street address ( $locale = 'fr_CA' )
    $street_address = "123 Main St";
    $locale = 'fr_CA';
    $parsedStreetAddress = CRM_Core_BAO_Address::parseStreetAddress($street_address, $locale);
    $this->assertEquals($parsedStreetAddress['street_name'], 'Main St');
    $this->assertEquals($parsedStreetAddress['street_number'], '123');
    $this->assertNotContains('street_unit', $parsedStreetAddress);
    $this->assertNotContains('street_number_suffix', $parsedStreetAddress);

    $listing = array(
        array(
          "src" => "18 Bld Jean-Sébastien BACH",
          "expected" => array(
            'street_name' => 'jean-sébastien bach',
            'street_unit' => 'bd',
            'street_number' => '18',
            'street_number_suffix' => '',
            )
              ),
         array(
             "src" => "13 av. A. Briand",
             "expected" => array(
               'street_name' => 'a. briand',
               'street_unit' => 'av',
               'street_number' => '13',
               'street_number_suffix' => '',
               )
                 ),
         array(
         "src" => "17, boulevard de la Victoire",
         "expected" => array(
           'street_name' => 'de la victoire',
           'street_unit' => 'bd',
           'street_number' => '17',
           'street_number_suffix' => '',
           )
             ),
         array(
             "src" => "8 Avenue de l'Europe",
             "expected" => array(
               'street_name' => "de l'europe",
               'street_unit' => 'av',
               'street_number' => '8',
               'street_number_suffix' => '',
               )
                 ),
         array(
             "src" => "17B rue du Soleil",
             "expected" => array(
               'street_name' => 'du soleil',
               'street_unit' => 'rue',
               'street_number' => '17',
               'street_number_suffix' => 'b',
               )
                 ),
         array(
             "src" => "1 a rue de Wangenbourg",
             "expected" => array(
               'street_name' => 'de wangenbourg',
               'street_unit' => 'rue',
               'street_number' => '1',
               'street_number_suffix' => 'a',
               )
                 ),
         array(
                 "src" => "2 Av. du Gal De Gaulle",
                 "expected" => array(
                   'street_name' => 'du gal de gaulle',
                   'street_unit' => 'av',
                   'street_number' => '2',
                   'street_number_suffix' => '',
                   )
                     ),
         array(
             "src" => "2, rue Saint-Pierre-le-Jeune",
             "expected" => array(
               'street_name' => 'saint-pierre-le-jeune',
               'street_unit' => 'rue',
               'street_number' => '2',
               'street_number_suffix' => '',
               )
                 ),
         array(
             "src" => "14a rue de la Couronne",
             "expected" => array(
               'street_name' => 'de la couronne',
               'street_unit' => 'rue',
               'street_number' => '14',
               'street_number_suffix' => 'a',
               )
                 ),
         array(
             "src" => "12, rue du docteur Maurice Freysz",
             "expected" => array(
               'street_name' => 'du docteur maurice freysz',
               'street_unit' => 'rue',
               'street_number' => '12',
               'street_number_suffix' => '',
               )
                 ),
         array(
             "src" => "103 Grand'Rue",
             "expected" => array(
               'street_name' => 'grand\'rue',
               'street_unit' => '',
               'street_number' => '103',
               'street_number_suffix' => '',
               )
                 ),
         array(
             "src" => "2 rue Jacques et René Knecht",
             "expected" => array(
               'street_name' => 'jacques et rené knecht',
               'street_unit' => 'rue',
               'street_number' => '2',
               'street_number_suffix' => '',
               )
                 ),
         array(
             "src" => "2 rue Jacques et René Knecht",
             "expected" => array(
               'street_name' => 'jacques et rené knecht',
               'street_unit' => 'rue',
               'street_number' => '2',
               'street_number_suffix' => '',
               )
                 ),
         array(
             "src" => "69b route de Lyon",
             "expected" => array(
               'street_name' => 'de lyon',
               'street_unit' => 'rte',
               'street_number' => '69',
               'street_number_suffix' => 'b',
               )
                 ),
         array(
             "src" => "36 Jardins de la Moder",
             "expected" => array(
               'street_name' => 'de la moder',
               'street_unit' => 'jard',
               'street_number' => '36',
               'street_number_suffix' => '',
               )
                 ),
         array(
             "src" => "20 A rue rue de Lièpvre",
             "expected" => array(
               'street_name' => 'de lièpvre',
               'street_unit' => 'rue',
               'street_number' => '20',
               'street_number_suffix' => 'a',
               )
                 ),
         array(
             "src" => "7A rue de L'Ermitage",
             "expected" => array(
               'street_name' => "de l'ermitage",
               'street_unit' => 'rue',
               'street_number' => '7',
               'street_number_suffix' => 'a',
               )
                 ),
         array(
             "src" => "29, rue Chauveau Lagarde",
             "expected" => array(
               'street_name' => 'chauveau lagarde',
               'street_unit' => 'rue',
               'street_number' => '29',
               'street_number_suffix' => '',
               )
                 ),
         array(
             "src" => "18,rue du Landsberg",
             "expected" => array(
               'street_name' => 'du landsberg',
               'street_unit' => 'rue',
               'street_number' => '18',
               'street_number_suffix' => '',
               )
                 ),
             array(
                 "src" => "7 rue de la Saure (chambre 313)",
                 "fail" => true,
                 "expected" => array(
                   'street_name' => 'de la saure',
                   'street_unit' => 'rue',
                   'street_number' => '7',
                   'street_number_suffix' => '',
                   )
                     ),
             array(
                 "src" => "41 j, avenue du Petit Senn",
                 "expected" => array(
                   'street_name' => 'du petit senn',
                   'street_unit' => 'av',
                   'street_number' => '41',
                   'street_number_suffix' => 'j',
                   )
                     ),
             array(
                 "src" => "30 rue des Mouins Maison de retraite Niederbourg",
                 "expected" => array(
                   'street_name' => 'des mouins maison de retraite niederbourg',
                   'street_unit' => 'rue',
                   'street_number' => '30',
                   'street_number_suffix' => '',
                   )
                     ),
             array(
                 "src" => "164, rue Roger Salengro",
                 "expected" => array(
                   'street_name' => 'roger salengro',
                   'street_unit' => 'rue',
                   'street_number' => '164',
                   'street_number_suffix' => '',
                   )
                     ),
                 array(
                     "src" => "23 A rue du Député Hallez",
                     "expected" => array(
                       'street_name' => 'du député hallez',
                       'street_unit' => 'rue',
                       'street_number' => '23',
                       'street_number_suffix' => 'a',
                       )
                         ),
                 array(
                     "src" => "2 cour du bain des juifs",
                     "expected" => array(
                       'street_name' => 'du bain des juifs',
                       'street_unit' => 'cr',
                       'street_number' => '2',
                       'street_number_suffix' => '',
                       )
                         ),
                 array(
                     "src" => "78 BD Clemenceau",
                     "expected" => array(
                       'street_name' => 'clemenceau',
                       'street_unit' => 'bd',
                       'street_number' => '78',
                       'street_number_suffix' => '',
                       )
                         ),
                 array(
                     "src" => "10 ROUTE DE SAVERNE",
                     "expected" => array(
                       'street_name' => 'de saverne',
                       'street_unit' => 'rte',
                       'street_number' => '10',
                       'street_number_suffix' => '',
                       )
                         ),
                     array(
                         "src" => "17 av des Consulats",
                         "expected" => array(
                           'street_name' => 'des consulats',
                           'street_unit' => 'av',
                           'street_number' => '17',
                           'street_number_suffix' => '',
                           )
                             ),
                         array(
                             "src" => "59 Bd de l'Europe",
                             "expected" => array(
                               'street_name' => 'de l\'europe',
                               'street_unit' => 'bd',
                               'street_number' => '59',
                               'street_number_suffix' => '',
                               )
                                 ),
                             array(
                                 "src" => "26 av. Schutzenberger",
                                 "expected" => array(
                                   'street_name' => 'schutzenberger',
                                   'street_unit' => 'av',
                                   'street_number' => '26',
                                   'street_number_suffix' => '',
                                   )
                                     ),
                                 array(
                                     "src" => "66b rue du Château",
                                     "expected" => array(
                                       'street_name' => 'du château',
                                       'street_unit' => 'rue',
                                       'street_number' => '66',
                                       'street_number_suffix' => 'b',
                                       )
                                         ),
                                     array(
                                         "src" => "38 route de St Léonard",
                                         "expected" => array(
                                           'street_name' => 'de st léonard',
                                           'street_unit' => 'rte',
                                           'street_number' => '38',
                                           'street_number_suffix' => '',
                                           )
                                             ),
                                         array(
                                             "src" => "rue de l'etang",
                                             "expected" => array(
                                               'street_name' => "de l'etang",
                                               'street_unit' => 'rue',
                                               'street_number' => '',
                                               'street_number_suffix' => '',
                                               )
                                                 ),
                                             array(
                                                 "src" => "275c rue de l'Eglise",
                                                 "expected" => array(
                                                   'street_name' => "de l'eglise",
                                                   'street_unit' => 'rue',
                                                   'street_number' => '275',
                                                   'street_number_suffix' => 'c',
                                                   )
                                                     ),
                                                 array(
                                                     "src" => "322e rue des Jardins",
                                                     "expected" => array(
                                                       'street_name' => 'des jardins',
                                                       'street_unit' => 'rue',
                                                       'street_number' => '322',
                                                       'street_number_suffix' => 'e',
                                                       )
                                                         ),
                                                     array(
                                                         "src" => "43 chemin du Rosenmeer",
                                                         "expected" => array(
                                                           'street_name' => 'du rosenmeer',
                                                           'street_unit' => 'chem',
                                                           'street_number' => '43',
                                                           'street_number_suffix' => '',
                                                           )
                                                             ),
                                                         array(
                                                             "src" => "10 Haute Corniche",
                                                             "expected" => array(
                                                               'street_name' => 'haute corniche',
                                                               'street_unit' => '',
                                                               'street_number' => '10',
                                                               'street_number_suffix' => '',
                                                               )
                                                                 ),
                                                             array(
                                                                 "src" => "Berges de l'Ehn",
                                                                 "expected" => array(
                                                                   'street_name' => 'de l\'ehn',
                                                                   'street_unit' => 'berges',
                                                                   'street_number' => '',
                                                                   'street_number_suffix' => '',
                                                                   )
                                                                     ),
                                                                 array(
                                                                     "src" => "Résidence \"Les Aubépines\"",
                                                                     "expected" => array(
                                                                       'street_name' => 'résidence "les aubépines"',
                                                                       'street_unit' => '',
                                                                       'street_number' => '',
                                                                       'street_number_suffix' => '',
                                                                       )
                                                                         ),
                                                                     array(
                                                                         "src" => "17 place de l'Etoile",
                                                                         "expected" => array(
                                                                           'street_name' => 'de l\'etoile',
                                                                           'street_unit' => 'pl',
                                                                           'street_number' => '17',
                                                                           'street_number_suffix' => '',
                                                                           )
                                                                             ),
                                                                         array(
                                                                             "src" => "23 lotissement St Jean",
                                                                             "expected" => array(
                                                                               'street_name' => 'st jean',
                                                                               'street_unit' => 'lot',
                                                                               'street_number' => '23',
                                                                               'street_number_suffix' => '',
                                                                               )
                                                                                 ),
                                                                             array(
                                                                                 "src" => "3 Cours Charles Spindler route d'altenheim",
                                                                                 "expected" => array(
                                                                                   'street_name' => 'charles spindler route d\'altenheim',
                                                                                   'street_unit' => 'crs',
                                                                                   'street_number' => '3',
                                                                                   'street_number_suffix' => '',
                                                                                   )
                                                                                     ),
                                                                                 array(
                                                                                     "src" => "Résidence \"Solarium\"",
                                                                                     "expected" => array(
                                                                                       'street_name' => 'résidence "solarium"',
                                                                                       'street_unit' => '',
                                                                                       'street_number' => '',
                                                                                       'street_number_suffix' => '',
                                                                                       )
                                                                                         ),
                                                                                     array(
                                                                                         "src" => "11 av de Gail",
                                                                                         "expected" => array(
                                                                                           'street_name' => 'de gail',
                                                                                           'street_unit' => 'av',
                                                                                           'street_number' => '11',
                                                                                           'street_number_suffix' => '',
                                                                                           )
                                                                                             ),
                                                                                         array(
                                                                                             "src" => "510 rue du Tramway",
                                                                                             "expected" => array(
                                                                                               'street_name' => 'du tramway',
                                                                                               'street_unit' => 'rue',
                                                                                               'street_number' => '510',
                                                                                               'street_number_suffix' => '',
                                                                                               )
                                                                                                 ),
                     array(
                         "src" => "Presbytère catholique lieu dit foegel",
                         "expected" => array(
                           'street_name' => 'presbytère catholique lieu dit foegel',
                           'street_unit' => '',
                           'street_number' => '',
                           'street_number_suffix' => '',
                           )
                             ),
                         array(
                             "src" => "5 Belle Vue cour du chapitre",
                             "expected" => array(
                               'street_name' => 'belle vue cour du chapitre',
                               'street_unit' => '',
                               'street_number' => '5',
                               'street_number_suffix' => '',
                               )
                                 ),
                             array(
                                 "src" => "44E Rue du Maréchal Koenig",
                                 "expected" => array(
                                   'street_name' => 'du maréchal koenig',
                                   'street_unit' => 'rue',
                                   'street_number' => '44',
                                   'street_number_suffix' => 'e',
                                   )
                                     ),
                                 array(
                                     "src" => "Villa Lumière",
                                     "expected" => array(
                                       'street_name' => 'lumière',
                                       'street_unit' => 'vla',
                                       'street_number' => '',
                                       'street_number_suffix' => '',
                                       )
                                         ),
                                     array(
                                         "src" => "11 allée Hohle-Felsen",
                                         "expected" => array(
                                           'street_name' => 'hohle-felsen',
                                           'street_unit' => 'all',
                                           'street_number' => '11',
                                           'street_number_suffix' => '',
                                           )
                                             ),
                                         array(
                                             "src" => "44.2 Bd de l'Europe",
                                             "expected" => array(
                                               'street_name' => "de l'europe",
                                               'street_unit' => 'bd',
                                               'street_number' => '44.2',
                                               'street_number_suffix' => '',
                                               )
                                                 ),
                                             array(
                                                 "src" => "100 Les Hauts de Klingenthal",
                                                 "expected" => array(
                                                   'street_name' => 'les hauts de klingenthal',
                                                   'street_unit' => '',
                                                   'street_number' => '100',
                                                   'street_number_suffix' => '',
                                                   )
                                                     ),
                                                 array(
                                                     "src" => "Foyer Résidence Hohenbourg",
                                                     "expected" => array(
                                                       'street_name' => 'foyer résidence hohenbourg',
                                                       'street_unit' => '',
                                                       'street_number' => '',
                                                       'street_number_suffix' => '',
                                                       )
                                                         ),
                                                     array(
                                                         "src" => "via Mme DOPPLER",
                                                         "expected" => array(
                                                           'street_name' => 'mme doppler',
                                                           'street_unit' => 'via',
                                                           'street_number' => '',
                                                           'street_number_suffix' => '',
                                                           )
                                                             ),
                                                         array(
                                                             "src" => "8 Rempart Joffre",
                                                             "expected" => array(
                                                               'street_name' => 'rempart joffre',
                                                               'street_unit' => '',
                                                               'street_number' => '8',
                                                               'street_number_suffix' => '',
                                                               )
                                                                 ),
                                                             array(
                                                                 "src" => "Moyenne Corniche",
                                                                 "expected" => array(
                                                                   'street_name' => 'moyenne corniche',
                                                                   'street_unit' => '',
                                                                   'street_number' => '',
                                                                   'street_number_suffix' => '',
                                                                   )
                                                                     ),
                                                                 array(
                                                                     "src" => "111a rue de Valff",
                                                                     "expected" => array(
                                                                       'street_name' => 'de valff',
                                                                       'street_unit' => 'rue',
                                                                       'street_number' => '111',
                                                                       'street_number_suffix' => 'a',
                                                                       )
                                                                         ),
                                                                     array(
                                                                         "src" => "21 av des Consulats - Bât A impasse de l'ehn",
                                                                         "expected" => array(
                                                                           'street_name' => 'des consulats - bât a impasse de l\'ehn',
                                                                           'street_unit' => 'av',
                                                                           'street_number' => '21',
                                                                           'street_number_suffix' => '',
                                                                           )
                                                                             ),
                                                                         array(
                                                                             "src" => "6 place Saint Louis fondation goethe",
                                                                             "expected" => array(
                                                                               'street_name' => 'saint louis fondation goethe',
                                                                               'street_unit' => 'pl',
                                                                               'street_number' => '6',
                                                                               'street_number_suffix' => '',
                                                                               )
                                                                                 ),
                                                                             array(
                                                                                 "src" => "Chez M. Alain MASTRONARDI",
                                                                                 "expected" => array(
                                                                                   'street_name' => 'chez m. alain mastronardi',
                                                                                   'street_unit' => '',
                                                                                   'street_number' => '',
                                                                                   'street_number_suffix' => '',
                                                                                   )
                                                                                     ),
                                                                                 array(
                                                                                     "src" => "19b route de Laubenheim",
                                                                                     "expected" => array(
                                                                                       'street_name' => 'de laubenheim',
                                                                                       'street_unit' => 'rte',
                                                                                       'street_number' => '19',
                                                                                       'street_number_suffix' => 'b',
                                                                                       )
                                                                                         ),
                                                                                     array(
                                                                                         "src" => "8 rue de la Poste",
                                                                                         "expected" => array(
                                                                                           'street_name' => 'de la poste',
                                                                                           'street_unit' => 'rue',
                                                                                           'street_number' => '8',
                                                                                           'street_number_suffix' => '',
                                                                                           )
                                                                                             ),
                                                                                         array(
                                                                                             "src" => "1ter rue de l'Eglise",
                                                                                             "expected" => array(
                                                                                               'street_name' => "de l'eglise",
                                                                                               'street_unit' => 'rue',
                                                                                               'street_number' => '1',
                                                                                               'street_number_suffix' => 'ter',
                                                                                               )
                                                                                                 ),
                                                                                             array(
                                                                                                 "src" => "1a quai Saint Thomas",
                                                                                                 "expected" => array(
                                                                                                   'street_name' => 'saint thomas',
                                                                                                   'street_unit' => 'quai',
                                                                                                   'street_number' => '1',
                                                                                                   'street_number_suffix' => 'a',
                                                                                                   )
                                                                                                     ),
                                                                                                 array(
                                                                                                     "src" => "1bis rue de la Gare",
                                                                                                     "expected" => array(
                                                                                                       'street_name' => 'de la gare',
                                                                                                       'street_unit' => 'rue',
                                                                                                       'street_number' => '1',
                                                                                                       'street_number_suffix' => 'bis',
                                                                                                       )
                                                                                                         ),
                                                                                                     array(
                                                                                                         "src" => "4 rue du Bouclier",
                                                                                                         "expected" => array(
                                                                                                           'street_name' => 'du bouclier',
                                                                                                           'street_unit' => 'rue',
                                                                                                           'street_number' => '4',
                                                                                                           'street_number_suffix' => '',
                                                                                                           )
                                                                                                             )
                         );



    $locale = 'fr_FR';

    $line = 0;
    foreach( $listing as $sample )
    {
      $result = CRM_Core_BAO_Address::parseAddress($sample["src"], "fr_FR");
      //$result = CRM_Core_BAO_Address::parseFrenchAddress($sample["src"], "fr_FR");
      $expect = $sample["expected"];
      $fail = (array_key_exists("fail",$sample))?($sample["fail"])?true:false:false;


      if($fail)
      {
        $this->assertNotEquals($expect['street_name'], $result['street_name'], "Failed[$line]: Street name mismatch:'".$result['street_name']."' vs '".$expect['street_name']."'\n");
        //$this->assertNotEquals($expect['street_unit'], $result['street_unit'], "Failed[$line]: Street unit mismatch:'".$result['street_unit']."' vs '".$expect['street_unit']."'\n");
        //$this->assertNotEquals($expect['street_number'], $result['street_number'], "Failed[$line]: Street number mismatch:'".$result['street_number']."' vs '".$expect['street_number']."'\n");
        //$this->assertNotEquals($expect['street_number_suffix'], $result['street_number_suffix'], "Failed[$line]: Street number suffix mismatch:'".$result['street_number_suffix']." vs '".$expect['street_number_suffix']."<br>\n");
      }
      else
      {
        $this->assertEquals($expect['street_name'], $result['street_name'], "Failed[$line]: Street name mismatch:'".$result['street_name']."' vs '".$expect['street_name']."'<br>\n");
        $this->assertEquals($expect['street_unit'], $result['street_unit'], "Failed[$line]: Street unit mismatch:'".$result['street_unit']." vs '".$expect['street_unit']."<br>\n");
        $this->assertEquals($expect['street_number'], $result['street_number'], "Failed[$line]: Street number mismatch:'".$result['street_number']."' vs '".$expect['street_number']."'<br>\n");
        $this->assertEquals($expect['street_number_suffix'], $result['street_number_suffix'], "Failed[$line]: Street number suffix mismatch:'".$result['street_number_suffix']." vs '".$expect['street_number_suffix']."<br>\n");
      }

      $line ++;
    }
  }
 public function testParseFrenchAddress() {
    $listing = array(
        array(
          "src" => "18 Bld Jean-Sébastien BACH",
          "expected" => array(
            'street_name' => 'jean-sébastien bach',
            'street_unit' => 'bd',
            'street_number' => '18',
            'street_number_suffix' => '',
            )
              ),
          array(
              "src" => "13 av. A. Briand",
              "expected" => array(
                'street_name' => 'a. briand',
                'street_unit' => 'av',
                'street_number' => '13',
                'street_number_suffix' => '',
                )
                  ),
              array(
                  "src" => "17, boulevard de la Victoire",
                  "expected" => array(
                    'street_name' => 'de la victoire',
                    'street_unit' => 'bd',
                    'street_number' => '17',
                    'street_number_suffix' => '',
                    )
                      ),
                  array(
                      "src" => "8 Avenue de l'Europe",
                      "expected" => array(
                        'street_name' => "de l'europe",
                        'street_unit' => 'av',
                        'street_number' => '8',
                        'street_number_suffix' => '',
                        )
                          ),
                      array(
                          "src" => "17B rue du Soleil",
                          "expected" => array(
                            'street_name' => 'du soleil',
                            'street_unit' => 'rue',
                            'street_number' => '17',
                            'street_number_suffix' => 'b',
                            )
                              ),
                          array(
                              "src" => "1 a rue de Wangenbourg",
                              "expected" => array(
                                'street_name' => 'de wangenbourg',
                                'street_unit' => 'rue',
                                'street_number' => '1',
                                'street_number_suffix' => 'a',
                                )
                                  ),
                              array(
                                  "src" => "2 Av. du Gal De Gaulle",
                                  "expected" => array(
                                    'street_name' => 'du gal de gaulle',
                                    'street_unit' => 'av',
                                    'street_number' => '2',
                                    'street_number_suffix' => '',
                                    )
                                      ),
                                  array(
                                      "src" => "2, rue Saint-Pierre-le-Jeune",
                                      "expected" => array(
                                        'street_name' => 'saint-pierre-le-jeune',
                                        'street_unit' => 'rue',
                                        'street_number' => '2',
                                        'street_number_suffix' => '',
                                        )
                                          ),
                                      array(
                                          "src" => "14a rue de la Couronne",
                                          "expected" => array(
                                            'street_name' => 'de la couronne',
                                            'street_unit' => 'rue',
                                            'street_number' => '14',
                                            'street_number_suffix' => 'a',
                                            )
                                              ),
                                          array(
                                              "src" => "12, rue du docteur Maurice Freysz",
                                              "expected" => array(
                                                'street_name' => 'du docteur maurice freysz',
                                                'street_unit' => 'rue',
                                                'street_number' => '12',
                                                'street_number_suffix' => '',
                                                )
                                                  ),
                                              array(
                                                  "src" => "103 Grand'Rue",
                                                  "expected" => array(
                                                    'street_name' => 'grand\'rue',
                                                    'street_unit' => '',
                                                    'street_number' => '103',
                                                    'street_number_suffix' => '',
                                                    )
                                                      ),
                                                  array(
                                                      "src" => "2 rue Jacques et René Knecht",
                                                      "expected" => array(
                                                        'street_name' => 'jacques et rené knecht',
                                                        'street_unit' => 'rue',
                                                        'street_number' => '2',
                                                        'street_number_suffix' => '',
                                                        )
                                                          ),
                                                      array(
                                                          "src" => "2 rue Jacques et René Knecht",
                                                          "expected" => array(
                                                            'street_name' => 'jacques et rené knecht',
                                                            'street_unit' => 'rue',
                                                            'street_number' => '2',
                                                            'street_number_suffix' => '',
                                                            )
                                                              ),
                                                          array(
                                                              "src" => "69b route de Lyon",
                                                              "expected" => array(
                                                                'street_name' => 'de lyon',
                                                                'street_unit' => 'rte',
                                                                'street_number' => '69',
                                                                'street_number_suffix' => 'b',
                                                                )
                                                                  ),
                                                              array(
                                                                  "src" => "36 Jardins de la Moder",
                                                                  "expected" => array(
                                                                    'street_name' => 'de la moder',
                                                                    'street_unit' => 'jard',
                                                                    'street_number' => '36',
                                                                    'street_number_suffix' => '',
                                                                    )
                                                                      ),
                                                                  array(
                                                                      "src" => "20 A rue rue de Lièpvre",
                                                                      "expected" => array(
                                                                        'street_name' => 'de lièpvre',
                                                                        'street_unit' => 'rue',
                                                                        'street_number' => '20',
                                                                        'street_number_suffix' => 'a',
                                                                        )
                                                                          ),
                                                                      array(
                                                                          "src" => "7A rue de L'Ermitage",
                                                                          "expected" => array(
                                                                            'street_name' => "de l'ermitage",
                                                                            'street_unit' => 'rue',
                                                                            'street_number' => '7',
                                                                            'street_number_suffix' => 'a',
                                                                            )
                                                                              ),
                                                                          array(
                                                                              "src" => "29, rue Chauveau Lagarde",
                                                                              "expected" => array(
                                                                                'street_name' => 'chauveau lagarde',
                                                                                'street_unit' => 'rue',
                                                                                'street_number' => '29',
                                                                                'street_number_suffix' => '',
                                                                                )
                                                                                  ),
                                                                              array(
                                                                                  "src" => "18,rue du Landsberg",
                                                                                  "expected" => array(
                                                                                    'street_name' => 'du landsberg',
                                                                                    'street_unit' => 'rue',
                                                                                    'street_number' => '18',
                                                                                    'street_number_suffix' => '',
                                                                                    )
                                                                                      ),
                                                                                  array(
                                                                                      "src" => "7 rue de la Saure (chambre 313)",
                                                                                      "fail" => true,
                                                                                      "expected" => array(
                                                                                        'street_name' => 'de la saure',
                                                                                        'street_unit' => 'rue',
                                                                                        'street_number' => '7',
                                                                                        'street_number_suffix' => '',
                                                                                        )
                                                                                          ),
                                                                                      array(
                                                                                          "src" => "41 j, avenue du Petit Senn",
                                                                                          "expected" => array(
                                                                                            'street_name' => 'du petit senn',
                                                                                            'street_unit' => 'av',
                                                                                            'street_number' => '41',
                                                                                            'street_number_suffix' => 'j',
                                                                                            )
                                                                                              ),
                                                                                          array(
                                                                                              "src" => "30 rue des Mouins Maison de retraite Niederbourg",
                                                                                              "expected" => array(
                                                                                                'street_name' => 'des mouins maison de retraite niederbourg',
                                                                                                'street_unit' => 'rue',
                                                                                                'street_number' => '30',
                                                                                                'street_number_suffix' => '',
                                                                                                )
                                                                                                  ),
                                                                                              array(
                                                                                                  "src" => "164, rue Roger Salengro",
                                                                                                  "expected" => array(
                                                                                                    'street_name' => 'roger salengro',
                                                                                                    'street_unit' => 'rue',
                                                                                                    'street_number' => '164',
                                                                                                    'street_number_suffix' => '',
                                                                                                    )
                                                                                                      ),
                                                                                                  array(
                                                                                                      "src" => "23 A rue du Député Hallez",
                                                                                                      "expected" => array(
                                                                                                        'street_name' => 'du député hallez',
                                                                                                        'street_unit' => 'rue',
                                                                                                        'street_number' => '23',
                                                                                                        'street_number_suffix' => 'a',
                                                                                                        )
                                                                                                          ),
                                                                                                      array(
                                                                                                          "src" => "2 cour du bain des juifs",
                                                                                                          "expected" => array(
                                                                                                            'street_name' => 'du bain des juifs',
                                                                                                            'street_unit' => 'cr',
                                                                                                            'street_number' => '2',
                                                                                                            'street_number_suffix' => '',
                                                                                                            )
                                                                                                              ),
                                                                                                          array(
                                                                                                              "src" => "78 BD Clemenceau",
                                                                                                              "expected" => array(
                                                                                                                'street_name' => 'clemenceau',
                                                                                                                'street_unit' => 'bd',
                                                                                                                'street_number' => '78',
                                                                                                                'street_number_suffix' => '',
                                                                                                                )
                                                                                                                  ),
                                                                                                              array(
                                                                                                                  "src" => "10 ROUTE DE SAVERNE",
                                                                                                                  "expected" => array(
                                                                                                                    'street_name' => 'de saverne',
                                                                                                                    'street_unit' => 'rte',
                                                                                                                    'street_number' => '10',
                                                                                                                    'street_number_suffix' => '',
                                                                                                                    )
                                                                                                                      ),
                                                                                                                  array(
                                                                                                                      "src" => "17 av des Consulats",
                                                                                                                      "expected" => array(
                                                                                                                        'street_name' => 'des consulats',
                                                                                                                        'street_unit' => 'av',
                                                                                                                        'street_number' => '17',
                                                                                                                        'street_number_suffix' => '',
                                                                                                                        )
                                                                                                                          ),
                                                                                                                      array(
                                                                                                                          "src" => "59 Bd de l'Europe",
                                                                                                                          "expected" => array(
                                                                                                                            'street_name' => 'de l\'europe',
                                                                                                                            'street_unit' => 'bd',
                                                                                                                            'street_number' => '59',
                                                                                                                            'street_number_suffix' => '',
                                                                                                                            )
                                                                                                                              ),
                                                                                                                          array(
                                                                                                                              "src" => "26 av. Schutzenberger",
                                                                                                                              "expected" => array(
                                                                                                                                'street_name' => 'schutzenberger',
                                                                                                                                'street_unit' => 'av',
                                                                                                                                'street_number' => '26',
                                                                                                                                'street_number_suffix' => '',
                                                                                                                                )
                                                                                                                                  ),
                                                                                                                              array(
                                                                                                                                  "src" => "66b rue du Château",
                                                                                                                                  "expected" => array(
                                                                                                                                    'street_name' => 'du château',
                                                                                                                                    'street_unit' => 'rue',
                                                                                                                                    'street_number' => '66',
                                                                                                                                    'street_number_suffix' => 'b',
                                                                                                                                    )
                                                                                                                                      ),
                                                                                                                                  array(
                                                                                                                                      "src" => "38 route de St Léonard",
                                                                                                                                      "expected" => array(
                                                                                                                                        'street_name' => 'de st léonard',
                                                                                                                                        'street_unit' => 'rte',
                                                                                                                                        'street_number' => '38',
                                                                                                                                        'street_number_suffix' => '',
                                                                                                                                        )
                                                                                                                                          ),
                                                                                                                                      array(
                                                                                                                                          "src" => "rue de l'etang",
                                                                                                                                          "expected" => array(
                                                                                                                                            'street_name' => "de l'etang",
                                                                                                                                            'street_unit' => 'rue',
                                                                                                                                            'street_number' => '',
                                                                                                                                            'street_number_suffix' => '',
                                                                                                                                            )
                                                                                                                                              ),
                                                                                                                                          array(
                                                                                                                                              "src" => "275c rue de l'Eglise",
                                                                                                                                              "expected" => array(
                                                                                                                                                'street_name' => "de l'eglise",
                                                                                                                                                'street_unit' => 'rue',
                                                                                                                                                'street_number' => '275',
                                                                                                                                                'street_number_suffix' => 'c',
                                                                                                                                                )
                                                                                                                                                  ),
                                                                                                                                              array(
                                                                                                                                                  "src" => "322e rue des Jardins",
                                                                                                                                                  "expected" => array(
                                                                                                                                                    'street_name' => 'des jardins',
                                                                                                                                                    'street_unit' => 'rue',
                                                                                                                                                    'street_number' => '322',
                                                                                                                                                    'street_number_suffix' => 'e',
                                                                                                                                                    )
                                                                                                                                                      ),
                                                                                                                                                  array(
                                                                                                                                                      "src" => "43 chemin du Rosenmeer",
                                                                                                                                                      "expected" => array(
                                                                                                                                                        'street_name' => 'du rosenmeer',
                                                                                                                                                        'street_unit' => 'chem',
                                                                                                                                                        'street_number' => '43',
                                                                                                                                                        'street_number_suffix' => '',
                                                                                                                                                        )
                                                                                                                                                          ),
                                                                                                                                                      array(
                                                                                                                                                          "src" => "10 Haute Corniche",
                                                                                                                                                          "expected" => array(
                                                                                                                                                            'street_name' => 'haute corniche',
                                                                                                                                                            'street_unit' => '',
                                                                                                                                                            'street_number' => '10',
                                                                                                                                                            'street_number_suffix' => '',
                                                                                                                                                            )
                                                                                                                                                              ),
                                                                                                                                                          array(
                                                                                                                                                              "src" => "Berges de l'Ehn",
                                                                                                                                                              "expected" => array(
                                                                                                                                                                'street_name' => 'de l\'ehn',
                                                                                                                                                                'street_unit' => 'berges',
                                                                                                                                                                'street_number' => '',
                                                                                                                                                                'street_number_suffix' => '',
                                                                                                                                                                )
                                                                                                                                                                  ),
                                                                                                                                                              array(
                                                                                                                                                                  "src" => "Résidence \"Les Aubépines\"",
                                                                                                                                                                  "expected" => array(
                                                                                                                                                                    'street_name' => 'résidence "les aubépines"',
                                                                                                                                                                    'street_unit' => '',
                                                                                                                                                                    'street_number' => '',
                                                                                                                                                                    'street_number_suffix' => '',
                                                                                                                                                                    )
                                                                                                                                                                      ),
                                                                                                                                                                  array(
                                                                                                                                                                      "src" => "17 place de l'Etoile",
                                                                                                                                                                      "expected" => array(
                                                                                                                                                                        'street_name' => 'de l\'etoile',
                                                                                                                                                                        'street_unit' => 'pl',
                                                                                                                                                                        'street_number' => '17',
                                                                                                                                                                        'street_number_suffix' => '',
                                                                                                                                                                        )
                                                                                                                                                                          ),
                                                                                                                                                                      array(
                                                                                                                                                                          "src" => "23 lotissement St Jean",
                                                                                                                                                                          "expected" => array(
                                                                                                                                                                            'street_name' => 'st jean',
                                                                                                                                                                            'street_unit' => 'lot',
                                                                                                                                                                            'street_number' => '23',
                                                                                                                                                                            'street_number_suffix' => '',
                                                                                                                                                                            )
                                                                                                                                                                              ),
                                                                                                                                                                          array(
                                                                                                                                                                              "src" => "3 Cours Charles Spindler route d'altenheim",
                                                                                                                                                                              "expected" => array(
                                                                                                                                                                                'street_name' => 'charles spindler route d\'altenheim',
                                                                                                                                                                                'street_unit' => 'crs',
                                                                                                                                                                                'street_number' => '3',
                                                                                                                                                                                'street_number_suffix' => '',
                                                                                                                                                                                )
                                                                                                                                                                                  ),
                                                                                                                                                                              array(
                                                                                                                                                                                  "src" => "Résidence \"Solarium\"",
                                                                                                                                                                                  "expected" => array(
                                                                                                                                                                                    'street_name' => 'résidence "solarium"',
                                                                                                                                                                                    'street_unit' => '',
                                                                                                                                                                                    'street_number' => '',
                                                                                                                                                                                    'street_number_suffix' => '',
                                                                                                                                                                                    )
                                                                                                                                                                                      ),
                                                                                                                                                                                  array(
                                                                                                                                                                                      "src" => "11 av de Gail",
                                                                                                                                                                                      "expected" => array(
                                                                                                                                                                                        'street_name' => 'de gail',
                                                                                                                                                                                        'street_unit' => 'av',
                                                                                                                                                                                        'street_number' => '11',
                                                                                                                                                                                        'street_number_suffix' => '',
                                                                                                                                                                                        )
                                                                                                                                                                                          ),
                                                                                                                                                                                      array(
                                                                                                                                                                                          "src" => "510 rue du Tramway",
                                                                                                                                                                                          "expected" => array(
                                                                                                                                                                                            'street_name' => 'du tramway',
                                                                                                                                                                                            'street_unit' => 'rue',
                                                                                                                                                                                            'street_number' => '510',
                                                                                                                                                                                            'street_number_suffix' => '',
                                                                                                                                                                                            )
                                                                                                                                                                                              ),
                                                                                                                                                                                          array(
                                                                                                                                                                                              "src" => "Presbytère catholique lieu dit foegel",
                                                                                                                                                                                              "expected" => array(
                                                                                                                                                                                                'street_name' => 'presbytère catholique lieu dit foegel',
                                                                                                                                                                                                'street_unit' => '',
                                                                                                                                                                                                'street_number' => '',
                                                                                                                                                                                                'street_number_suffix' => '',
                                                                                                                                                                                                )
                                                                                                                                                                                                  ),
                                                                                                                                                                                              array(
                                                                                                                                                                                                  "src" => "5 Belle Vue cour du chapitre",
                                                                                                                                                                                                  "expected" => array(
                                                                                                                                                                                                    'street_name' => 'belle vue cour du chapitre',
                                                                                                                                                                                                    'street_unit' => '',
                                                                                                                                                                                                    'street_number' => '5',
                                                                                                                                                                                                    'street_number_suffix' => '',
                                                                                                                                                                                                    )
                                                                                                                                                                                                      ),
                                                                                                                                                                                                  array(
                                                                                                                                                                                                      "src" => "44E Rue du Maréchal Koenig",
                                                                                                                                                                                                      "expected" => array(
                                                                                                                                                                                                        'street_name' => 'du maréchal koenig',
                                                                                                                                                                                                        'street_unit' => 'rue',
                                                                                                                                                                                                        'street_number' => '44',
                                                                                                                                                                                                        'street_number_suffix' => 'e',
                                                                                                                                                                                                        )
                                                                                                                                                                                                          ),
                                                                                                                                                                                                      array(
                                                                                                                                                                                                          "src" => "Villa Lumière",
                                                                                                                                                                                                          "expected" => array(
                                                                                                                                                                                                            'street_name' => 'lumière',
                                                                                                                                                                                                            'street_unit' => 'vla',
                                                                                                                                                                                                            'street_number' => '',
                                                                                                                                                                                                            'street_number_suffix' => '',
                                                                                                                                                                                                            )
                                                                                                                                                                                                              ),
                                                                                                                                                                                                          array(
                                                                                                                                                                                                              "src" => "11 allée Hohle-Felsen",
                                                                                                                                                                                                              "expected" => array(
                                                                                                                                                                                                                'street_name' => 'hohle-felsen',
                                                                                                                                                                                                                'street_unit' => 'all',
                                                                                                                                                                                                                'street_number' => '11',
                                                                                                                                                                                                                'street_number_suffix' => '',
                                                                                                                                                                                                                )
                                                                                                                                                                                                                  ),
                                                                                                                                                                                                              array(
                                                                                                                                                                                                                  "src" => "44.2 Bd de l'Europe",
                                                                                                                                                                                                                  "expected" => array(
                                                                                                                                                                                                                    'street_name' => "de l'europe",
                                                                                                                                                                                                                    'street_unit' => 'bd',
                                                                                                                                                                                                                    'street_number' => '44.2',
                                                                                                                                                                                                                    'street_number_suffix' => '',
                                                                                                                                                                                                                    )
                                                                                                                                                                                                                      ),
                                                                                                                                                                                                                  array(
                                                                                                                                                                                                                      "src" => "100 Les Hauts de Klingenthal",
                                                                                                                                                                                                                      "expected" => array(
                                                                                                                                                                                                                        'street_name' => 'les hauts de klingenthal',
                                                                                                                                                                                                                        'street_unit' => '',
                                                                                                                                                                                                                        'street_number' => '100',
                                                                                                                                                                                                                        'street_number_suffix' => '',
                                                                                                                                                                                                                        )
                                                                                                                                                                                                                          ),
                                                                                                                                                                                                                      array(
                                                                                                                                                                                                                          "src" => "Foyer Résidence Hohenbourg",
                                                                                                                                                                                                                          "expected" => array(
                                                                                                                                                                                                                            'street_name' => 'foyer résidence hohenbourg',
                                                                                                                                                                                                                            'street_unit' => '',
                                                                                                                                                                                                                            'street_number' => '',
                                                                                                                                                                                                                            'street_number_suffix' => '',
                                                                                                                                                                                                                            )
                                                                                                                                                                                                                              ),
                                                                                                                                                                                                                          array(
                                                                                                                                                                                                                              "src" => "via Mme DOPPLER",
                                                                                                                                                                                                                              "expected" => array(
                                                                                                                                                                                                                                'street_name' => 'mme doppler',
                                                                                                                                                                                                                                'street_unit' => 'via',
                                                                                                                                                                                                                                'street_number' => '',
                                                                                                                                                                                                                                'street_number_suffix' => '',
                                                                                                                                                                                                                                )
                                                                                                                                                                                                                                  ),
                                                                                                                                                                                                                              array(
                                                                                                                                                                                                                                  "src" => "8 Rempart Joffre",
                                                                                                                                                                                                                                  "expected" => array(
                                                                                                                                                                                                                                    'street_name' => 'rempart joffre',
                                                                                                                                                                                                                                    'street_unit' => '',
                                                                                                                                                                                                                                    'street_number' => '8',
                                                                                                                                                                                                                                    'street_number_suffix' => '',
                                                                                                                                                                                                                                    )
                                                                                                                                                                                                                                      ),
                                                                                                                                                                                                                                  array(
                                                                                                                                                                                                                                      "src" => "Moyenne Corniche",
                                                                                                                                                                                                                                      "expected" => array(
                                                                                                                                                                                                                                        'street_name' => 'moyenne corniche',
                                                                                                                                                                                                                                        'street_unit' => '',
                                                                                                                                                                                                                                        'street_number' => '',
                                                                                                                                                                                                                                        'street_number_suffix' => '',
                                                                                                                                                                                                                                        )
                                                                                                                                                                                                                                          ),
                                                                                                                                                                                                                                      array(
                                                                                                                                                                                                                                          "src" => "111a rue de Valff",
                                                                                                                                                                                                                                          "expected" => array(
                                                                                                                                                                                                                                            'street_name' => 'de valff',
                                                                                                                                                                                                                                            'street_unit' => 'rue',
                                                                                                                                                                                                                                            'street_number' => '111',
                                                                                                                                                                                                                                            'street_number_suffix' => 'a',
                                                                                                                                                                                                                                            )
                                                                                                                                                                                                                                              ),
                                                                                                                                                                                                                                          array(
                                                                                                                                                                                                                                              "src" => "21 av des Consulats - Bât A impasse de l'ehn",
                                                                                                                                                                                                                                              "expected" => array(
                                                                                                                                                                                                                                                'street_name' => 'des consulats - bât a impasse de l\'ehn',
                                                                                                                                                                                                                                                'street_unit' => 'av',
                                                                                                                                                                                                                                                'street_number' => '21',
                                                                                                                                                                                                                                                'street_number_suffix' => '',
                                                                                                                                                                                                                                                )
                                                                                                                                                                                                                                                  ),
                                                                                                                                                                                                                                              array(
                                                                                                                                                                                                                                                  "src" => "6 place Saint Louis fondation goethe",
                                                                                                                                                                                                                                                  "expected" => array(
                                                                                                                                                                                                                                                    'street_name' => 'saint louis fondation goethe',
                                                                                                                                                                                                                                                    'street_unit' => 'pl',
                                                                                                                                                                                                                                                    'street_number' => '6',
                                                                                                                                                                                                                                                    'street_number_suffix' => '',
                                                                                                                                                                                                                                                    )
                                                                                                                                                                                                                                                      ),
                                                                                                                                                                                                                                                  array(
                                                                                                                                                                                                                                                      "src" => "Chez M. Alain MASTRONARDI",
                                                                                                                                                                                                                                                      "expected" => array(
                                                                                                                                                                                                                                                        'street_name' => 'chez m. alain mastronardi',
                                                                                                                                                                                                                                                        'street_unit' => '',
                                                                                                                                                                                                                                                        'street_number' => '',
                                                                                                                                                                                                                                                        'street_number_suffix' => '',
                                                                                                                                                                                                                                                        )
                                                                                                                                                                                                                                                          ),
                                                                                                                                                                                                                                                      array(
                                                                                                                                                                                                                                                          "src" => "19b route de Laubenheim",
                                                                                                                                                                                                                                                          "expected" => array(
                                                                                                                                                                                                                                                            'street_name' => 'de laubenheim',
                                                                                                                                                                                                                                                            'street_unit' => 'rte',
                                                                                                                                                                                                                                                            'street_number' => '19',
                                                                                                                                                                                                                                                            'street_number_suffix' => 'b',
                                                                                                                                                                                                                                                            )
                                                                                                                                                                                                                                                              ),
                                                                                                                                                                                                                                                          array(
                                                                                                                                                                                                                                                              "src" => "8 rue de la Poste",
                                                                                                                                                                                                                                                              "expected" => array(
                                                                                                                                                                                                                                                                'street_name' => 'de la poste',
                                                                                                                                                                                                                                                                'street_unit' => 'rue',
                                                                                                                                                                                                                                                                'street_number' => '8',
                                                                                                                                                                                                                                                                'street_number_suffix' => '',
                                                                                                                                                                                                                                                                )
                                                                                                                                                                                                                                                                  ),
                                                                                                                                                                                                                                                              array(
                                                                                                                                                                                                                                                                  "src" => "1ter rue de l'Eglise",
                                                                                                                                                                                                                                                                  "expected" => array(
                                                                                                                                                                                                                                                                    'street_name' => "de l'eglise",
                                                                                                                                                                                                                                                                    'street_unit' => 'rue',
                                                                                                                                                                                                                                                                    'street_number' => '1',
                                                                                                                                                                                                                                                                    'street_number_suffix' => 'ter',
                                                                                                                                                                                                                                                                    )
                                                                                                                                                                                                                                                                      ),
                                                                                                                                                                                                                                                                  array(
                                                                                                                                                                                                                                                                      "src" => "1a quai Saint Thomas",
                                                                                                                                                                                                                                                                      "expected" => array(
                                                                                                                                                                                                                                                                        'street_name' => 'saint thomas',
                                                                                                                                                                                                                                                                        'street_unit' => 'quai',
                                                                                                                                                                                                                                                                        'street_number' => '1',
                                                                                                                                                                                                                                                                        'street_number_suffix' => 'a',
                                                                                                                                                                                                                                                                        )
                                                                                                                                                                                                                                                                          ),
                                                                                                                                                                                                                                                                      array(
                                                                                                                                                                                                                                                                          "src" => "1bis rue de la Gare",
                                                                                                                                                                                                                                                                          "expected" => array(
                                                                                                                                                                                                                                                                            'street_name' => 'de la gare',
                                                                                                                                                                                                                                                                            'street_unit' => 'rue',
                                                                                                                                                                                                                                                                            'street_number' => '1',
                                                                                                                                                                                                                                                                            'street_number_suffix' => 'bis',
                                                                                                                                                                                                                                                                            )
                                                                                                                                                                                                                                                                              ),
                                                                                                                                                                                                                                                                          array(
                                                                                                                                                                                                                                                                              "src" => "4 rue du Bouclier",
                                                                                                                                                                                                                                                                              "expected" => array(
                                                                                                                                                                                                                                                                                'street_name' => 'du bouclier',
                                                                                                                                                                                                                                                                                'street_unit' => 'rue',
                                                                                                                                                                                                                                                                                'street_number' => '4',
                                                                                                                                                                                                                                                                                'street_number_suffix' => '',
                                                                                                                                                                                                                                                                                )
                                                                                                                                                                                                                                                                                  )
                                                                                                                                                                                                                                                                                  );

    //$parsedStreetAddress = CRM_Core_BAO_Address::parseStreetAddress($street_address);
    $parser = AddressParserFactory::createParser("fr_FR");


    $line = 0;
    foreach( $listing as $sample )
    {
      $result = $parser->parseStreetAddress($sample["src"]);
      $expect = $sample["expected"];
      $fail = (array_key_exists("fail",$sample))?($sample["fail"])?true:false:false;


      if($fail)
      {
      $this->assertNotEquals($expect['street_name'], $result['street_name'], "Failed[$line]: Street name mismatch:'".$result['street_name']."' vs '".$expect['street_name']."'\n");
       //$this->assertNotEquals($expect['street_unit'], $result['street_unit'], "Failed[$line]: Street unit mismatch:'".$result['street_unit']."' vs '".$expect['street_unit']."'\n");
       //$this->assertNotEquals($expect['street_number'], $result['street_number'], "Failed[$line]: Street number mismatch:'".$result['street_number']."' vs '".$expect['street_number']."'\n");
       //$this->assertNotEquals($expect['street_number_suffix'], $result['street_number_suffix'], "Failed[$line]: Street number suffix mismatch:'".$result['street_number_suffix']." vs '".$expect['street_number_suffix']."<br>\n");
      }
      else
      {
      $this->assertEquals($expect['street_name'], $result['street_name'], "Failed[$line]: Street name mismatch:'".$result['street_name']."' vs '".$expect['street_name']."'<br>\n");
      $this->assertEquals($expect['street_unit'], $result['street_unit'], "Failed[$line]: Street unit mismatch:'".$result['street_unit']." vs '".$expect['street_unit']."<br>\n");
      $this->assertEquals($expect['street_number'], $result['street_number'], "Failed[$line]: Street number mismatch:'".$result['street_number']."' vs '".$expect['street_number']."'<br>\n");
      $this->assertEquals($expect['street_number_suffix'], $result['street_number_suffix'], "Failed[$line]: Street number suffix mismatch:'".$result['street_number_suffix']." vs '".$expect['street_number_suffix']."<br>\n");
      }

      $line ++;
    }
  }



  /**
   * @dataProvider supportedAddressParsingLocales
   */
  public function testIsSupportedByAddressParsingReturnTrueForSupportedLocales($locale) {
    $isSupported = CRM_Core_BAO_Address::isSupportedParsingLocale($locale);
    $this->assertTrue($isSupported);
  }

  /**
   * @dataProvider supportedAddressParsingLocales
   */
  public function testIsSupportedByAddressParsingReturnTrueForSupportedDefaultLocales($locale) {
    CRM_Core_Config::singleton()->lcMessages = $locale;
    $isSupported = CRM_Core_BAO_Address::isSupportedParsingLocale();
    $this->assertTrue($isSupported);

  }

  public function supportedAddressParsingLocales() {
    return [
      ['en_US'],
      ['en_CA'],
      ['fr_CA'],
    ];
  }

  /**
   * @dataProvider sampleOFUnsupportedAddressParsingLocales
   */
  public function testIsSupportedByAddressParsingReturnFalseForUnSupportedLocales($locale) {
    $isNotSupported = CRM_Core_BAO_Address::isSupportedParsingLocale($locale);
    $this->assertFalse($isNotSupported);
  }

  /**
   * @dataProvider sampleOFUnsupportedAddressParsingLocales
   */
  public function testIsSupportedByAddressParsingReturnFalseForUnSupportedDefaultLocales($locale) {
    CRM_Core_Config::singleton()->lcMessages = $locale;
    $isNotSupported = CRM_Core_BAO_Address::isSupportedParsingLocale();
    $this->assertFalse($isNotSupported);
  }

  public function sampleOFUnsupportedAddressParsingLocales() {
    return [
      ['en_GB'],
      ['af_ZA'],
      ['da_DK'],
    ];
  }

  /**
   * CRM-21214 - Ensure all child addresses are updated correctly - 1.
   * 1. First, create three contacts: A, B, and C
   * 2. Create an address for contact A
   * 3. Use contact A's address for contact B
   * 4. Use contact B's address for contact C
   * 5. Change contact A's address
   * Address of Contact C should reflect contact A's address change
   * Also, Contact C's address' master_id should be Contact A's address id.
   */
  public function testSharedAddressChaining1(): void {
    $contactIdA = $this->individualCreate([], 0);
    $contactIdB = $this->individualCreate([], 1);
    $contactIdC = $this->individualCreate([], 2);

    $addressParamsA = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdA,
    ];
    $addAddressA = CRM_Core_BAO_Address::writeRecord($addressParamsA);

    $addressParamsB = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'master_id' => $addAddressA->id,
      'contact_id' => $contactIdB,
      ];
    $addAddressB = CRM_Core_BAO_Address::writeRecord($addressParamsB);

    $addressParamsC = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'master_id' => $addAddressB->id,
      'contact_id' => $contactIdC,
      ];
    $addAddressC = CRM_Core_BAO_Address::writeRecord($addressParamsC);

    $updatedAddressParamsA = [
      'id' => $addAddressA->id,
      'street_address' => '1313 New Address Lane',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdA,
      ];
    $updatedAddressA = CRM_Core_BAO_Address::writeRecord($updatedAddressParamsA);

    // CRM-21214 - Has Address C been updated with Address A's new values?
    $newAddressC = new CRM_Core_DAO_Address();
    $newAddressC->id = $addAddressC->id;
    $newAddressC->find(TRUE);
    $newAddressC->fetch(TRUE);

    $this->assertEquals($updatedAddressA->street_address, $newAddressC->street_address);
    $this->assertEquals($updatedAddressA->id, $newAddressC->master_id);
  }

  /**
   * CRM-21214 - Ensure all child addresses are updated correctly - 2.
   * 1. First, create three contacts: A, B, and C
   * 2. Create an address for contact A and B
   * 3. Use contact A's address for contact C
   * 4. Use contact B's address for contact A
   * 5. Change contact B's address
   * Address of Contact C should reflect contact B's address change
   * Also, Contact C's address' master_id should be Contact B's address id.
   */
  public function testSharedAddressChaining2(): void {
    $contactIdA = $this->individualCreate([], 0);
    $contactIdB = $this->individualCreate([], 1);
    $contactIdC = $this->individualCreate([], 2);

    $addressParamsA = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdA,
    ];
    $addAddressA = CRM_Core_BAO_Address::writeRecord($addressParamsA);

    $addressParamsB = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdB,
    ];
    $addAddressB = CRM_Core_BAO_Address::writeRecord($addressParamsB);

    $addressParamsC = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'master_id' => $addAddressA->id,
      'contact_id' => $contactIdC,
      ];
    $addAddressC = CRM_Core_BAO_Address::writeRecord($addressParamsC);

    $updatedAddressParamsA = [
      'id' => $addAddressA->id,
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'master_id' => $addAddressB->id,
      'contact_id' => $contactIdA,
      ];
    $updatedAddressA = CRM_Core_BAO_Address::writeRecord($updatedAddressParamsA);

    $updatedAddressParamsB = [
      'id' => $addAddressB->id,
      'street_address' => '1313 New Address Lane',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdB,
      ];
    $updatedAddressB = CRM_Core_BAO_Address::writeRecord($updatedAddressParamsB);

    // CRM-21214 - Has Address C been updated with Address B's new values?
    $newAddressC = new CRM_Core_DAO_Address();
    $newAddressC->id = $addAddressC->id;
    $newAddressC->find(TRUE);
    $newAddressC->fetch(TRUE);

    $this->assertEquals($updatedAddressB->street_address, $newAddressC->street_address);
    $this->assertEquals($updatedAddressB->id, $newAddressC->master_id);
  }

  /**
   * CRM-21214 - Ensure all child addresses are updated correctly - 3.
   * 1. First, create a contact: A
   * 2. Create an address for contact A
   * 3. Use contact A's address for contact A's address
   * An error should be given, and master_id should remain the same.
   */
  public function testSharedAddressChaining3(): void {
    $contactIdA = $this->individualCreate([], 0);

    $addressParamsA = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdA,
    ];
    $addAddressA = CRM_Core_BAO_Address::writeRecord($addressParamsA);

    $updatedAddressParamsA = [
      'id' => $addAddressA->id,
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'master_id' => $addAddressA->id,
      'contact_id' => $contactIdA,
      ];
    $updatedAddressA = CRM_Core_BAO_Address::writeRecord($updatedAddressParamsA);

    // CRM-21214 - AdressA shouldn't be master of itself.
    $this->assertEmpty($updatedAddressA->master_id);
  }

  /**
   * dev/dev/core#1670 - Ensure that the custom fields on adresses are copied
   * to inherited address
   * 1. test the creation of the shared address with custom field
   * 2. test the update of the custom field in the master
   */
  public function testSharedAddressCustomField(): void {

    $this->createCustomGroupWithFieldOfType(['extends' => 'Address'], 'text');
    $customField = $this->getCustomFieldName('text');

    $contactIdA = $this->individualCreate([], 0);
    $contactIdB = $this->individualCreate([], 1);

    $addressParamsA = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'contact_id' => $contactIdA,
      $customField => 'this is a custom text field',
      ];
    $addressParamsA['custom'] = CRM_Core_BAO_CustomField::postProcess($addressParamsA, NULL, 'Address');

    $addAddressA = CRM_Core_BAO_Address::writeRecord($addressParamsA);

    // without having the custom field, we should still copy the values from master
    $addressParamsB = [
      'street_address' => '123 Fake St.',
      'location_type_id' => '1',
      'is_primary' => '1',
      'master_id' => $addAddressA->id,
      'contact_id' => $contactIdB,
      ];
    $addAddressB = CRM_Core_BAO_Address::writeRecord($addressParamsB);

    // 1. check if the custom fields values have been copied from master to shared address
    $address = $this->callAPISuccessGetSingle('Address', ['id' => $addAddressB->id, 'return' => $this->getCustomFieldName('text')]);
    $this->assertEquals($addressParamsA[$customField], $address[$customField]);

    // 2. now, we update addressA custom field to see if it goes into addressB
    $addressParamsA['id'] = $addAddressA->id;
    $addressParamsA[$customField] = 'updated custom text field';
    $addressParamsA['custom'] = CRM_Core_BAO_CustomField::postProcess($addressParamsA, NULL, 'Address');
    CRM_Core_BAO_Address::writeRecord($addressParamsA);

    $address = $this->callAPISuccessGetSingle('Address', ['id' => $addAddressB->id, 'return' => $this->getCustomFieldName('text')]);
    $this->assertEquals($addressParamsA[$customField], $address[$customField]);

  }

  /**
   * Pinned countries with Default country
   */
  public function testPinnedCountriesWithDefaultCountry(): void {
    // Guyana, Netherlands, United States
    $pinnedCountries = ['1093', '1152', '1228'];

    // set default country to Netherlands
    $this->callAPISuccess('Setting', 'create', ['defaultContactCountry' => 1152, 'pinnedContactCountries' => $pinnedCountries]);
    // get the list of country
    $availableCountries = CRM_Core_PseudoConstant::country(FALSE, FALSE);
    // get the order of country id using their keys
    $availableCountries = array_keys($availableCountries);

    // default country is set, so first country should be Netherlands, then rest from pinned countries.

    // Netherlands
    $this->assertEquals(1152, $availableCountries[0]);
    // Guyana
    $this->assertEquals(1093, $availableCountries[1]);
    // United States
    $this->assertEquals(1228, $availableCountries[2]);
  }

  /**
   * Pinned countries with out Default country
   */
  public function testPinnedCountriesWithOutDefaultCountry(): void {
    // Guyana, Netherlands, United States
    $pinnedCountries = ['1093', '1152', '1228'];

    // unset default country
    $this->callAPISuccess('Setting', 'create', ['defaultContactCountry' => NULL, 'pinnedContactCountries' => $pinnedCountries]);

    // get the list of country
    $availableCountries = CRM_Core_PseudoConstant::country(FALSE, FALSE);
    // get the order of country id using their keys
    $availableCountries = array_keys($availableCountries);

    // no default country, so sequnece should be present as per pinned countries.

    // Guyana
    $this->assertEquals(1093, $availableCountries[0]);
    // Netherlands
    $this->assertEquals(1152, $availableCountries[1]);
    // United States
    $this->assertEquals(1228, $availableCountries[2]);
  }

  /**
   * Test dev/core#2379 fix - geocodes shouldn't be > 14 characters.
   */
  public function testLongGeocodes(): void {
    $contactId = $this->individualCreate();

    $fixParams = [
      'street_address' => 'E 906N Pine Pl W',
      'supplemental_address_1' => 'Editorial Dept',
      'supplemental_address_2' => '',
      'supplemental_address_3' => '',
      'city' => 'El Paso',
      'postal_code' => '88575',
      'postal_code_suffix' => '',
      'state_province_id' => '1001',
      'country_id' => '1228',
      'geo_code_1' => '41.701308979563',
      'geo_code_2' => '-73.91941868639',
      'location_type_id' => '1',
      'is_primary' => '1',
      'is_billing' => '0',
      'contact_id' => $contactId,
      ];

    CRM_Core_BAO_Address::fixAddress($fixParams);
    $addAddress = CRM_Core_BAO_Address::writeRecord($fixParams);

    $addParams = $this->assertDBNotNull('CRM_Core_DAO_Address', $contactId, 'id', 'contact_id',
        'Database check for created contact address.'
        );

    $this->assertEquals('41.70130897956', $addAddress->geo_code_1, 'In line' . __LINE__);
    $this->assertEquals('-73.9194186863', $addAddress->geo_code_2, 'In line' . __LINE__);
  }

}
