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

class PluginInboxNotificationSmsSetting extends NotificationSetting {

   static function getTypeName($nb=0) {
      return __('Inbox notification configuration', 'inbox');
   }

   public function getEnableLabel() {
      return __('Enable notifications via Inbox', 'inbox');
   }

   static public function getMode() {
      return Notification_NotificationTemplate::MODE_SMS;
   }

   function showFormConfig($options = []) {
      global $CFG_GLPI;
      echo "<div class='alert alert-info'>" . __('Nothing to configure', 'inbox') . "</div>";
   }
}