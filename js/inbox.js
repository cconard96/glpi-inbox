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

(function() {
   window.inboxPlugin = function() {
      var self = this;
      this.inboxDropdown = null;
      this.ajax_root = '';

      var updateMessageCounter = function() {
         var count = $("#inbox-dropdown-messages li").length;
         $('#inbox-dropdown .inbox-counter').first().html(count + " " + __("Messages", 'inbox'));
      };

      this.showMessageDropdown = function(caller) {
         if (self.inboxDropdown === null) {
            $("<div id='inbox-dropdown'></div>").appendTo('body');
            self.inboxDropdown = $("#inbox-dropdown");
            $("<span class='bold'>Inbox<span class='inbox-counter right'>0 Messages</span></span><hr><ul id='inbox-dropdown-messages'></ul>").appendTo(self.inboxDropdown);
            var inboxURL = CFG_GLPI.root_doc + "/plugins/inbox/front/inbox.php";
            $("<hr class='faint'><a href='" + inboxURL + "'>View all messages</a>").appendTo(self.inboxDropdown);

            $.ajax({
               method: 'GET',
               url: (self.ajax_root + "getMessages.php"),
               success: function(data) {
                  if (data.length === 0) {
                     $("#inbox-dropdown-messages").html("<li>" + __('No unread messages', 'inbox') + "</li>");
                  } else {
                     $("#inbox-dropdown-messages").empty();
                     $.each(data, function(ind, message) {
                        $("<li class='message unread'><div class='bold'>" + message.subject.substring(0, 50) + "</div><div>" + message.message.substring(0, 100) + "</div></li>").appendTo("#inbox-dropdown-messages");
                     });
                  }
                  updateMessageCounter();
               }
            });
            self.inboxDropdown.hide();
         }

         self.inboxDropdown.toggle();
         self.inboxDropdown.position({
            my:        "right top",
            at:        "right bottom",
            of:        $(caller),
            collision: "fit"
         });
      };

      this.init = function() {
         self.ajax_root = CFG_GLPI.root_doc + "/plugins/inbox/ajax/";
         $("<a id='inbox-btn' href='#' title='" + __('View inbox', 'inbox') + "'><i class='fas fa-inbox'/></a>").insertAfter("#menu_all_button");
         $("#inbox-btn").on('click', function() {
            self.showMessageDropdown(this);
         });
      };
   };
})();

$(document).ready(function() {
   var inbox = new window.inboxPlugin();
   inbox.init();
});