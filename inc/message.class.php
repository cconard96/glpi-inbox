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

   function getAllReceivedForUser(int $users_id = null) {
      if ($users_id === null) {
         $users_id = Session::getLoginUserID();
      }

      return $this->find(['users_id_recipient' => $users_id]);
   }

   static function sendMessage(int $users_id, array $message_data) {
      $message = new self();
      $message->add([
         'users_id_sender'    => $message_data['users_id_sender'] ?? Session::getLoginUserID(true),
         'users_id_recipient' => $users_id,
         'subject'            => $message_data['subject'] ?? '',
         'message'            => $message_data['message'] ?? '',
         'date_send'          => $_SESSION['glpi_currenttime'],
         'itemtype'           => $message_data['itemtype'] ?? null,
         'items_id'           => $message_data['items_id'] ?? 0
      ]);
   }

   static function getActionBarForMessage($source, $type) {
      switch ($source) {
         case self::SOURCE_TICKET:
         case self::SOURCE_CHANGE:
         case self::SOURCE_PROBLEM:
            break;
      }
   }

   public static function showInbox() {
      global $CFG_GLPI;

      $message = new self();
      $messages = $message->getAllReceivedForUser();
      $user = new User();

      $out = "<table class='tab_cadre_fixe'>";
      $out .= "<thead><tr class='tab_bg_1'></tr><th>" . __('Sender', 'inbox') . "</th>";
      $out .= "<th>" . __('Message', 'inbox') . "</th>";
      $out .= "<th>" . __('Date received', 'inbox') . "</th></tr></thead>";
      foreach ($messages as $data) {
         if ($data['users_id_sender'] !== null) {
            $user->getFromDB($data['users_id_sender']);
            $sender_pic = Html::image(User::getThumbnailURLForPicture($user->fields['picture']), [
               'width'  => 50
            ]);
            $sender_name = getUserName($data['users_id_sender']);
         } else {
            $sender_pic = Html::image($CFG_GLPI["root_doc"]."/pics/picture_min.png", [
               'width'  => 50
            ]);
            $sender_name = __('System', 'inbox');
         }
         $out .= "<tr class='tab_bg_2'>";
         $out .= "<td>{$sender_pic}<br>{$sender_name}</td><td>{$data['subject']}<br>{$data['message']}</td><td>{$data['date_sent']}</td>";
         $out .= "</tr>";
      }
      $out .= "</table>";

      echo $out;
   }
}