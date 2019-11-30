<?php
/*
 -------------------------------------------------------------------------
 GLPI Inbox
 Copyright (C) 2019 by Curtis Conard
 https://github.com/cconard96/glpi-inbox
 -------------------------------------------------------------------------
 LICENSE
 This file is part of GLPI Inbox.
 GLPI Inbox is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 GLPI Inbox is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with GLPI Inbox. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');
header("Content-Type: application/json; charset=UTF-8", true);
Html::header_nocache();
Session::checkLoginUser();

$input = file_get_contents('php://input');
parse_str($input, $_REQUEST);

global $DB;
$params = [
   'date_read' => $_SESSION['glpi_currenttime']
];
if ($_REQUEST['id']) {
   // Read a single message (or multiple specific messages)
   $DB->update(PluginInboxMessage::getTable(), $params, [
      'id' => $_REQUEST['id'],
      'date_read' => null,
      'users_id_recipient'   => Session::getLoginUserID(true)
   ]);
} else {
   // Read all messages
   $DB->update(PluginInboxMessage::getTable(), $params, [
      'date_read' => null,
      'users_id_recipient'   => Session::getLoginUserID(true)
   ]);
}