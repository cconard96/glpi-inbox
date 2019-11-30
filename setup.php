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

define('PLUGIN_INBOX_VERSION', '1.0.0');
define('PLUGIN_INBOX_MIN_GLPI', '9.5.0');
define('PLUGIN_INBOX_MAX_GLPI', '9.6.0');

function plugin_init_inbox() {
   global $PLUGIN_HOOKS;
   $PLUGIN_HOOKS['csrf_compliant']['inbox'] = true;
   $PLUGIN_HOOKS['add_javascript']['inbox'][] = 'js/inbox.js';
   $PLUGIN_HOOKS['add_css']['inbox'][] = 'css/inbox.css';
   if ($_SESSION['glpipalette'] === 'darker') {
      $PLUGIN_HOOKS['add_css']['inbox'][] = 'css/inbox_dark.css';
   }

   Notification_NotificationTemplate::registerMode(
      Notification_NotificationTemplate::MODE_SMS,
      __('Inbox', 'inbox'),
      'inbox');
}

function plugin_version_inbox() {

   return [
      'name' => __("Inbox", 'inbox'),
      'version' => PLUGIN_INBOX_VERSION,
      'author'  => 'Curtis Conard',
      'license' => 'GPLv2',
      'homepage'=>'https://github.com/cconard96/glpi-inbox',
      'requirements'   => [
         'glpi'   => [
            'min' => PLUGIN_INBOX_MIN_GLPI,
            'max' => PLUGIN_INBOX_MAX_GLPI
         ]
      ]
   ];
}

function plugin_inbox_check_prerequisites() {
   if (!method_exists('Plugin', 'checkGlpiVersion')) {
      $version = preg_replace('/^((\d+\.?)+).*$/', '$1', GLPI_VERSION);
      $matchMinGlpiReq = version_compare($version, PLUGIN_INBOX_MIN_GLPI, '>=');
      $matchMaxGlpiReq = version_compare($version, PLUGIN_INBOX_MAX_GLPI, '<');
      if (!$matchMinGlpiReq || !$matchMaxGlpiReq) {
         echo vsprintf(
            'This plugin requires GLPI >= %1$s and < %2$s.',
            [
               PLUGIN_INBOX_MIN_GLPI,
               PLUGIN_INBOX_MAX_GLPI,
            ]
         );
         return false;
      }
   }
   return true;
}

function plugin_inbox_check_config()
{
   return true;
}