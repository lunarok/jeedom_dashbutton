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
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class dashbutton extends eqLogic {

  public static function deamon_info() {
    $return = array();
    $return['log'] = 'dashbutton_node';
    $return['state'] = 'nok';
    $pid = trim( shell_exec ('ps ax | grep "dashbutton/resources/dashbutton.js" | grep -v "grep" | wc -l') );
    if ($pid != '' && $pid != '0') {
      $return['state'] = 'ok';
    }
    $return['launchable'] = 'ok';
    if (count(eqLogic::byType('dashbutton',true)) == 0) {
      $return['launchable'] = 'ko';
    }
    return $return;
  }

  public static function deamon_start() {
    self::deamon_stop();
    $deamon_info = self::deamon_info();
    if ($deamon_info['launchable'] != 'ok') {
      throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
    }
    log::add('dashbutton', 'info', 'Lancement du démon dashbutton');

    $service_path = realpath(dirname(__FILE__) . '/../../resources/');

    $url = network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/dashbutton/core/api/jeeDash.php?apikey=' . jeedom::getApiKey('dashbutton');

    $i = 0;
    $count = count(eqLogic::byType('dashbutton',true));
    $conf = '';
    foreach (eqLogic::byType('dashbutton') as $dashbutton) {
      if ($count == 1) {
        $conf = $dashbutton->getConfiguration('uid');
      } else if ($i == 0) {
        $conf .= '"' . $dashbutton->getConfiguration('uid') . '"';
        $i = 1;
      } else {
        $conf .= ',"' . $dashbutton->getConfiguration('uid') . '"';
        $i = 2;
      }
    }
    if ($i == 2) {
       $conf = '\'[' . $conf . ']\'';
    }

    $cmd = 'nodejs ' . $service_path . '/dashbutton.js ' . $url . ' ' . $conf . ' ' . $i;

    log::add('dashbutton', 'debug', $cmd);
    $result = exec('sudo ' . $cmd . ' >> ' . log::getPathToLog('dashbutton_node') . ' 2>&1 &');
    if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
      log::add('dashbutton', 'error', $result);
      return false;
    }

    $i = 0;
    while ($i < 30) {
      $deamon_info = self::deamon_info();
      if ($deamon_info['state'] == 'ok') {
        break;
      }
      sleep(1);
      $i++;
    }
    if ($i >= 30) {
      log::add('dashbutton', 'error', 'Impossible de lancer le démon dashbutton, vérifiez le port', 'unableStartDeamon');
      return false;
    }
    message::removeAll('dashbutton', 'unableStartDeamon');
    log::add('dashbutton', 'info', 'Démon dashbutton lancé');
    return true;

  }

  public static function deamon_stop() {
    exec('kill $(ps aux | grep "dashbutton/resources/dashbutton.js" | awk \'{print $2}\')');
    log::add('dashbutton', 'info', 'Arrêt du service dashbutton');
    $deamon_info = self::deamon_info();
    if ($deamon_info['state'] == 'ok') {
      sleep(1);
      exec('kill -9 $(ps aux | grep "dashbutton/resources/dashbutton.js" | awk \'{print $2}\')');
    }
    $deamon_info = self::deamon_info();
    if ($deamon_info['state'] == 'ok') {
      sleep(1);
      exec('sudo kill -9 $(ps aux | grep "dashbutton/resources/dashbutton.js" | awk \'{print $2}\')');
    }
  }

  public static function dependancy_info() {
    $return = array();
    $return['log'] = 'dashbutton_dep';
    $request = realpath(dirname(__FILE__) . '/../../resources/node_modules/request');
    $return['progress_file'] = '/tmp/dashbutton_dep';
    if (is_dir($request)) {
      $return['state'] = 'ok';
    } else {
      $return['state'] = 'nok';
    }
    return $return;
  }

  public static function dependancy_install() {
    log::add('dashbutton','info','Installation des dépéndances nodejs');
    $resource_path = realpath(dirname(__FILE__) . '/../../resources');
    passthru('/bin/bash ' . $resource_path . '/nodejs.sh ' . $resource_path . ' dashbutton > ' . log::getPathToLog('dashbutton_dep') . ' 2>&1 &');
  }

  public static function removeIcon($_icon) {
    $path = dirname(__FILE__) . '/../../../../' . $_icon;
    if (file_exists($path)) {
        unlink($path);
    }
	return;
  }

   public static function listIcon() {
    $path = dirname(__FILE__) . '/../../doc/images/dashes';
    $files = scandir($path);
	$list = array();
    foreach ($files as $imgname){
    if (!in_array($imgname, ['.','..'])){
		$brand=ucfirst(explode( '.' , $imgname)[0]);
		$list[] = array('plugins/dashbutton/doc/images/dashes/' . $imgname,$brand);
		}
	}
	return $list;
  }

  public function preUpdate() {
    if ($this->getConfiguration('uid') == '') {
      throw new Exception(__('La MAC ne peut etre vide',__FILE__));
    }
  }

  public function preSave() {
    $this->setLogicalId($this->getConfiguration('uid'));
  }

  public function postAjax() {
    $dashbuttonCmd = dashbuttonCmd::byEqLogicIdAndLogicalId($this->getId(),'button');
    if (!is_object($dashbuttonCmd)) {
      $dashbuttonCmd = new dashbuttonCmd();
      $dashbuttonCmd->setName('button');
      $dashbuttonCmd->setEqLogic_id($this->getId());
      $dashbuttonCmd->setLogicalId('button');
      $dashbuttonCmd->setType('info');
      $dashbuttonCmd->setSubType('binary');
      $dashbuttonCmd->setConfiguration('returnStateValue',0);
      $dashbuttonCmd->setConfiguration('returnStateTime',1);
      $dashbuttonCmd->save();
    }
    dashbutton::deamon_stop();
    dashbutton::deamon_start();
  }
}

class dashbuttonCmd extends cmd {
}
