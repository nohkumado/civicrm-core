<?php
require_once("../interfaces/AddressParser.php");


class Fr_frAddressParser implements AddressParser
{
  public function parseAddress($record) {
    //TODO place holder
    //$result['street_name'] = $this->parseStreetAddress($record['street_name']);
  }
  public function parseStreetAddress($streetAddress) {

    $result = [
      'street_name' => '',
      'street_unit' => '',
      'street_number' => '',
      'street_number_suffix' => '',
    ];
    $src = $streetAddress = strtolower(trim($streetAddress));

    // Regex pattern to match French street numbers
    //$numberPattern = '/^(\d+)(?:[.]+\d*)\s*[,]*\s*(?:[bis|ter|quater]*)/i'; // Matches numbers like 123, 123bis, 123ter, etc.
    $suffixPattern = '/^(\d+)([\.a-z]+)(\d*)\s*[,]*/'; 
    $numberPattern = '/^(\d+)\s*[,]*/'; // Matches numbers like 123, 123bis, 123ter, etc.
    $tierPattern = '/^(\d+)\s+([a-z]{1,2})\s*[,]*\s+/'; 

    // Initialize variables to store parsed components
    $number = '';
    $numberSuffix = '';
    $unit = '';
    $name = '';

    if (preg_match($suffixPattern, $streetAddress,$matches)) 
    {
      //remove first number as stree number
      $number = $matches[1];
      $rest = isset($matches[2]) ? $matches[2] : '';
      $streetAddress = preg_replace($suffixPattern,"",$streetAddress);
      $result['street_number'] = $number;
      if(preg_match('/[a-z]+/', $rest)) $result['street_number_suffix'] = $rest;
      else
      {
        //print("===========extracted additional stuff: ".print_r($matches,true)."<br>\n");
        $result['street_number'] = trim($matches[0]);
      }
      // remove eventual trailing comma
      if (preg_match('/^[,]+\s*/', $streetAddress,$matches)) {
        $streetAddress = preg_replace('/^[,]+\s*/',"",$streetAddress);
        $streetAddress = trim($streetAddress);
      }
      //nothing more to extract
      $result['street_name'] = $streetAddress;

      //print("extracted $number,$palier,$numberSuffix, rest '$streetAddress'<br>\n");
    }
    else if (preg_match($tierPattern, $streetAddress,$matches)) 
    {
      //remove first number as stree number
      $number = $matches[1];
      $rest = isset($matches[2]) ? $matches[2] : '';
      $streetAddress = preg_replace($tierPattern,"",$streetAddress);
      $result['street_number'] = $number;
      if(preg_match('/[a-z]+/', $rest)) $result['street_number_suffix'] = $rest;
      else
        print("===========extracted additional stuff: ".print_r($matches,true)."<br>\n");
      // remove eventual trailing comma
      if (preg_match('/^[,]+\s*/', $streetAddress,$matches)) {
        $streetAddress = preg_replace('/^[,]+\s*/',"",$streetAddress);
      }
      //nothing more to extract
      $result['street_name'] = $streetAddress;

      //print("extracted $number,$palier,$numberSuffix, rest '$streetAddress'<br>\n");
    }
    else if (preg_match($numberPattern, $streetAddress,$matches)) 
    {
      //remove first number as stree number
      $number = $matches[1];
      $palier = isset($matches[2]) ? $matches[2] : '';
      $numberSuffix = isset($matches[3]) ? $matches[3] : '';
      $streetAddress = preg_replace($numberPattern,"",$streetAddress);
      $result['street_number'] = $number;
      // remove eventual trailing comma
      if (preg_match('/^[,]+\s*/', $streetAddress,$matches)) {
        $streetAddress = preg_replace('/^[,]+\s*/',"",$streetAddress);
      }
      if (preg_match('/^[\.]+\S+/', $streetAddress,$matches)) {
        print("===========extracted additional stuff: ".print_r($matches,true)."<br>\n");
        $streetAddress = preg_replace('/^[\.]+\S+/',"",$streetAddress);
      }
      //nothing more to extract
      $result['street_name'] = $streetAddress;

      //print("extracted $number,$palier,$numberSuffix, rest '$streetAddress'<br>\n");
    }
    //else print("no match in $streetAddress<br>\n");

    $orgstr = $streetAddress;
    $result = $this->extractVia($streetAddress,$result);
    //print("adress now: '$streetAddress'<br>\n");
    //if(strcmp($orgstr,$streetAddress) != 0) print("++++ not normal $streetAddress vs $orgstr vs $src <br>\n");

    return $result;
  }

  public function extractVia($streetAddress,&$result, $verb= false)
  {
    $abbreviations = array(
        "allée" => "all",
        "autoroute" => "a",
        "av" => "av",
        "avenue" => "av",
        "aérodrome" => "aerd",
        "aérogare" => "aerg",
        "balcon" => "balc",
        "barrage" => "brg",
        "barrière" => "barr",
        "bassin" => "bass",
        "berges" => "berges",
        "bld" => "bd",
        "bois" => "bois",
        "boulevard" => "bd",
        "butte" => "butt",
        "carrefour" => "carr",
        "caserne" => "cas",
        "centre commercial" => "ccal",
        "chalet" => "chal",
        "champ" => "chp",
        "chaussée" => "chau",
        "chemin départemental" => "cd",
        "chemin" => "chem",
        "château" => "chat",
        "cité" => "cite",
        "clairiere" => "clr",
        "climat" => "clim",
        "clos" => "clos",
        "contour" => "cont",
        "cote" => "cote",
        "cottage" => "cott",
        "cour" => "cr",
        "cours" => "crs",
        "domaine" => "dom",
        "escalier" => "esc",
        "esplanade" => "esp",
        "faubourg" => "fbg",
        "ferme" => "ferm",
        "fond" => "fond",
        "fontaine" => "font",
        "fort" => "fort",
        "forêt" => "frt",
        "fosse" => "foss",
        "galerie" => "gal",
        "gare" => "gare",
        "grande avenue" => "gdav",
        "grande place" => "gdpl",
        "grande rue" => "gdr",
        "grange" => "grge",
        "hameau" => "ham",
        "hippodrome" => "hipp",
        "ile" => "ile",
        "impasse" => "imp",
        "jardin" => "jard",
        "jardins" => "jard",
        "lieu-dit" =>  "ldt",
        "lotissement" => "lot",
        "mail" => "mail",
        "maison" => "mais",
        "marche" => "marc",
        "marché" => "mch",
        "mare" => "mare",
        "mont" => "mont",
        "montée" => "mnt",
        "métro" => "metr",
        "palais" => "pal",
        "parc" => "parc",
        "parking" => "pkg",
        "parvis" => "parv",
        "passage" => "pass",
        "pavillon" => "pav",
        "pelouse" => "pel",
        "petite rue" => "ptr",
        "pièce" => "piec",
        "place" => "pl",
        "plaine" => "plai",
        "point kilométrique" => "pk",
        "pointe" => "pnte",
        "pont" => "pont",
        "port" => "port",
        "porte" => "prte",
        "prairie" => "prai",
        "promenade" => "prom",
        "pré" => "pre",
        "quai" => "quai",
        "quartier" => "quar",
        "rampe" => "rpe",
        "rer" => "rer",
        "residence" => "res",
        "rond-point" => "rdpt",
        "route départementale" => "rd",
        "route nationale" => "rn",
        "route" => "rte",
        "rue" => "rue",
        "ruelle" => "rle",
        "sentier" => "sent",
        "square" => "sq",
        "station" => "st",
        "terrasse" => "terr",
        "tour" => "tour",
        "traverse" => "trav",
        "val" => "val",
        "vallée" => "vall",
        "venelle" => "ven",
        "via" => "via",
        "villa" => "vla",
        "voie communale" => "vc",
        "voie" => "voie",
        "zone artisanale" => "za",
        "zone industrielle" => "zi",
        "écluse" => "ecl",
        "étang" => "etg",
        );
    $streetAddress = trim($streetAddress);

    //next we suppose that the next part is the qualifier of the road
    $parts = explode(' ', $streetAddress);
    $road = array_shift($parts);
    foreach($abbreviations as $via => $abbrev)
    {
      $abbrev = trim($abbrev);
      $pat = '/^'.$abbrev.'\./';
      $patsp = '/^'.$abbrev.'$/';
      $patviasp = '/^'.$via.'\s*$/';
      if(preg_match($patviasp,$road))
      {
        $result['street_unit'] = trim($abbrev);
        $streetAddress = trim(preg_replace('/^'.$via.'\s+/',"",$streetAddress));
        $streetAddress = trim(preg_replace('/^'.$via.'\s+/',"",$streetAddress));
        break;
      }
      else if(preg_match($pat,$road))
      {
        $result['street_unit'] = trim($abbrev);
        $streetAddress = trim(preg_replace($pat,"",$streetAddress));
        break;
      }
      else if(preg_match($patsp,$road))
      {
        $result['street_unit'] = trim($abbrev);
        $streetAddress = join(" ",$parts);
        break;
      }
    }
    if(strlen($result['street_unit'])<= 0)
    {
      //hmmm lets check if in the suffix we don't have erroneously the via...
      $match  ="";
      foreach($abbreviations as $via => $abbrev)
      {
        if(strcmp($result['street_number_suffix'],$abbrev) == 0) 
        {
          $match = trim($abbrev);
          break;
        }

      }
      if(strlen($match) >0) 
      {
        $result['street_number_suffix'] = "";
        $result['street_unit'] = $match;
      }
      else if($verb)print("### ERROR no intelligible via[$road] in '$streetAddress'<br>\n");
    }
    $result['street_name'] = $streetAddress;
    return($result);
  }
}
