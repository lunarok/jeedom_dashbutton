<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class openpaths extends eqLogic {

  public static function dependancy_info() {
    $return = array();
    $return['log'] = 'openpaths_dep';
    $request = realpath(dirname(__FILE__) . '/../../node/node_modules/request');
    $return['progress_file'] = '/tmp/openpaths_dep';
    if (is_dir($request)) {
      $return['state'] = 'ok';
    } else {
      $return['state'] = 'nok';
    }
    return $return;
  }

  public static function dependancy_install() {
    log::add('openpaths','info','Installation des dépéndances nodejs');
    $resource_path = realpath(dirname(__FILE__) . '/../../resources');
    passthru('/bin/bash ' . $resource_path . '/nodejs.sh ' . $resource_path . ' > ' . log::getPathToLog('openpaths_dep') . ' 2>&1 &');
  }

  public static function cron5() {

    if (!config::byKey('internalPort')) {
      $url = config::byKey('internalProtocol') . config::byKey('internalAddr') . config::byKey('internalComplement') . '/core/api/jeeApi.php?api=' . config::byKey('api');
    } else {
      $url = config::byKey('internalProtocol') . config::byKey('internalAddr'). ':' . config::byKey('internalPort') . config::byKey('internalComplement') . '/core/api/jeeApi.php?api=' . config::byKey('api');
    }

    foreach (eqLogic::byType('openpaths', true) as $openpaths) {
      $sensor_path = realpath(dirname(__FILE__) . '/../../node');
      $cmd = 'nodejs ' . $sensor_path . '/openpaths.js ' . $url . ' ' . $openpaths->getConfiguration('key') . ' ' . $openpaths->getConfiguration('secret') . ' ' . $openpaths->getId();
      //log::add('openpaths','debug',$cmd);
      $result = exec($cmd . ' >> ' . log::getPathToLog('openpaths_node') . ' 2>&1 &');
    }
  }

  public function postSave() {
    $text = $this->getCmd(null, 'geoloc');
    if (!is_object($text)) {
      $text = new openpathsCmd();
      $text->setLogicalId('geoloc');
      $text->setIsVisible(0);
      $text->setName(__('Geoloc', __FILE__));
    }
    $text->setType('info');
    $text->setSubType('string');
    $text->setEqLogic_id($this->getId());
    $text->save();
  }

  public static function event() {

    $json = file_get_contents('php://input');
    //log::add('openpaths', 'debug', 'Body ' . print_r($json,true));
    $json = str_replace('[', '', $json);
    $json = str_replace(']', '', $json);
    $body = json_decode($json, true);
    $data = $body['data'];
    $json = json_decode($data, true);
    $lon = $json['lon'];
    $lat = $json['lat'];
    if ($lon != '' && $lat != '') {
      log::add('openpaths', 'debug', 'Longitude ' . $lon . ' latitude ' . $lat);

      $eqlogic = openpaths::byId(init('id'));
      $text = $eqlogic->getCmd(null, 'geoloc');
      $text->setConfiguration('value', $lat . ',' . $lon);
      $text->save();
      $text->event($lat . ',' . $lon);
      //log::add('openpaths', 'debug', $eqlogic->getConfiguration('geoloc'));
      $geoloc = str_replace('#','',$eqlogic->getConfiguration('geoloc'));
      if (!config::byKey('internalPort')) {
        $url = config::byKey('internalProtocol') . config::byKey('internalAddr') . config::byKey('internalComplement') . '/core/api/jeeApi.php?api=' . config::byKey('api');
      } else {
        $url = config::byKey('internalProtocol') . config::byKey('internalAddr'). ':' . config::byKey('internalPort') . config::byKey('internalComplement') . '/core/api/jeeApi.php?api=' . config::byKey('api');
      }
      $url = $url . '&type=geoloc&id=' . $geoloc . '&value=' . $lat . ',' . $lon;
      log::add('openpaths', 'debug', 'URL ' . $url);
      $result = file_get_contents($url);
    }

  }


}

class openpathsCmd extends cmd {
  public function execute($_options = null) {
  }

}

?>
