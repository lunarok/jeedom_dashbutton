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


class dashbutton extends eqLogic {


  public static function deamon_info() {
    $return = array();
    $return['log'] = 'dashbutton_node';
    $return['state'] = 'nok';
    $pid = trim( shell_exec ('ps ax | grep "dashbutton/resources/dashbutton.py" | grep -v "grep" | wc -l') );
    if ($pid != '' && $pid != '0') {
      $return['state'] = 'ok';
    }
    $return['launchable'] = 'ok';
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

    if (!config::byKey('internalPort')) {
      $url = config::byKey('internalProtocol') . config::byKey('internalAddr') . config::byKey('internalComplement') . '/core/api/jeeApi.php?api=' . config::byKey('api');
    } else {
      $url = config::byKey('internalProtocol') . config::byKey('internalAddr'). ':' . config::byKey('internalPort') . config::byKey('internalComplement') . '/core/api/jeeApi.php?api=' . config::byKey('api');
    }

    $cmd = 'python ' . $service_path . '/dashbutton.py ' . $url;

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
    exec('kill $(ps aux | grep "dashbutton/resources/dashbutton.py" | awk \'{print $2}\')');
    log::add('dashbutton', 'info', 'Arrêt du service dashbutton');
    $deamon_info = self::deamon_info();
    if ($deamon_info['state'] == 'ok') {
      sleep(1);
      exec('kill -9 $(ps aux | grep "dashbutton/resources/dashbutton.py" | awk \'{print $2}\')');
    }
    $deamon_info = self::deamon_info();
    if ($deamon_info['state'] == 'ok') {
      sleep(1);
      exec('sudo kill -9 $(ps aux | grep "dashbutton/resources/dashbutton.py" | awk \'{print $2}\')');
    }
  }

  public static function dependancy_info() {
    $return = array();
    $return['log'] = 'dashbutton_dep';
    $cmd = "dpkg -l | grep python-scapy";
        exec($cmd, $output, $return_var);
        if ($output[0] != "") {
          $return['state'] = 'ok';
        } else {
          $return['state'] = 'nok';
        }
    return $return;
  }

  public static function dependancy_install() {
    exec('sudo apt-get -y install python-scapy tcpdump >> ' . log::getPathToLog('unipi_dep') . ' 2>&1 &');
  }

  public static function event() {
    $uid = init('uid');
    $dashbutton = self::byLogicalId($uid, 'dashbutton');
    if (!is_object($dashbutton)) {
      if (config::byKey('include_mode','dashbutton') != 1) {
        return false;
      }
      $dashbutton = new dashbutton();
      $dashbutton->setEqType_name('dashbutton');
      $dashbutton->setLogicalId($uid);
      $dashbutton->setConfiguration('uid', $uid);
      $dashbutton->setName($uid);
      $dashbutton->setIsEnable(true);
      event::add('dashbutton::includeDevice',
      array(
        'state' => $state
      )
    );
  }
  $dashbutton->setConfiguration('lastCommunication', date('Y-m-d H:i:s'));
  $dashbutton->save();
  $dashbuttonCmd = dashbuttonCmd::byEqLogicIdAndLogicalId($dashbutton->getId(),'button');
  if (!is_object($dashbuttonCmd)) {
    $dashbuttonCmd = new dashbuttonCmd();
    $dashbuttonCmd->setName('button');
    $dashbuttonCmd->setEqLogic_id($dashbutton->getId());
    $dashbuttonCmd->setLogicalId('button');
    $dashbuttonCmd->setType('info');
    $dashbuttonCmd->setSubType('binary');
    $dashbuttonCmd->setConfiguration('returnStateValue',0);
    $dashbuttonCmd->setConfiguration('returnStateTime',1);
  }
  $dashbuttonCmd->setConfiguration('value', 1);
  $dashbuttonCmd->save();
  $dashbuttonCmd->event(1);

}

}


class dashbuttonCmd extends cmd {

}
