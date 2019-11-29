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

class PluginInboxMessage extends CommonDBTM {

   const SOURCE_DIRECT = 0;
   const SOURCE_TICKET = 1;
   const SOURCE_CHANGE = 2;
   const SOURCE_PROBLEM = 3;
   const SOURCE_PROJECT = 4;
   const SOURCE_SYSTEM = 5;

   const TYPE_APPROVAL_REQUEST = 0;
   const TYPE_APPROVAL_ANSWER = 1;
   const TYPE_FOLLOWUP = 2;
   const TYPE_TASK = 3;
   const TYPE_DOCUMENT = 4;
   const TYPE_STATUS = 5;

   static function getTypeName($nb = 0) {
      return _n('Message', 'Messages', 'inbox');
   }

   function prepareInputForAdd($input) {
      $input['subject'] = addslashes($input['subject']);
      $input['message'] = addslashes($input['message']);
      return $input;
   }

   function getAllReceivedForUser(int $users_id = null, $params = []) {
      $p = [
         'order'  => ['date_sent DESC'],
         'start'  => 0,
         'limit'  => null
      ];
      $p = array_replace($p, $params);

      if ($users_id === null) {
         $users_id = Session::getLoginUserID();
      }

      $messages = $this->find(['users_id_recipient' => $users_id], $p['order'], $p['limit']);
      // Inject additional data to be passed to the JS code
      foreach ($messages as &$message) {
         if ($message['itemtype'] !== null) {
            $message['_link'] = $message['itemtype']::getFormURLWithID($message['items_id']);
         }
      }
      return $messages;
   }

   static function sendMessage(int $users_id, array $message_data) {
      $message = new self();
      $message->add([
         'users_id_sender'    => $message_data['users_id_sender'] ?? Session::getLoginUserID(true),
         'users_id_recipient' => $users_id,
         'subject'            => $message_data['subject'] ?? '',
         'message'            => $message_data['message'] ?? '',
         'date_sent'          => $_SESSION['glpi_currenttime'],
         'itemtype'           => $message_data['itemtype'] ?? null,
         'items_id'           => $message_data['items_id'] ?? 0
      ]);
   }

   public static function showInbox() {
      // Just output the inbox container. The JS code will auto-magically inject the inbox into it.
      echo "<div id='glpi-inbox'></div>";
   }
}