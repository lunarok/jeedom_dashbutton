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
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

if (!jeedom::apiAccess(init('apikey'), 'dashbutton')) {
 echo __('Clef API non valide, vous n\'êtes pas autorisé à effectuer cette action (dashbutton)', __FILE__);
 die();
}

$uid = init('uid');
$dashbutton = dashbutton::byLogicalId($uid, 'dashbutton');
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

return true;
?>
