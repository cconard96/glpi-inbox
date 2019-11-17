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

function plugin_inbox_install() {
   global $DB;

   $migration = new Migration(PLUGIN_INBOX_VERSION);
   if (!$DB->tableExists('glpi_plugin_inbox_messages')) {
      $query = "CREATE TABLE `glpi_plugin_inbox_messages` (
                 `id` int(11) NOT NULL AUTO_INCREMENT,
                 `users_id_sender` int(11) DEFAULT NULL,
                 `users_id_recipient` int(11) NOT NULL,
                 `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                 `message` text COLLATE utf8_unicode_ci NOT NULL,
                 `date_sent` datetime DEFAULT NULL,
                 `date_read` datetime DEFAULT NULL,
                 `itemtype` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
                 `items_id` int(11) NOT NULL DEFAULT 0,
                 PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, 'Error creating Inbox messages table' . $DB->error());
   }
   return true;
}

function plugin_inbox_uninstall() {
   global $DB;

   $migration = new Migration(PLUGIN_INBOX_VERSION);
   $migration->dropTable('glpi_plugin_inbox_messages');

   return true;
}

function plugin_inbox_add_item($item) {
   $recipients = [2];
   $message = [
      'users_id_sender' => null
   ];

   switch ($item->getType()) {
      case 'Ticket':
         $message['subject']  = sprintf(__('New ticket (#%d) %s', 'inbox'), $item->getID(), $item->fields['name']);
         $message['message']  = Html::clean(Toolbox::unclean_cross_side_scripting_deep(nl2br($item->fields['content'])));
         $message['users_id_sender'] = $item->fields['users_id_recipient'];
         $message['itemtype'] = 'Ticket';
         $message['items_id'] = $item->getID();
         break;
      default:
         return false;
   }

   foreach ($recipients as $recipient) {
      PluginInboxMessage::sendMessage($recipient, $message);
   }
}

function plugin_inbox_update_item($item) {

}