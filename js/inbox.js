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
      this.messages = [];

      var getTeamBadge = function(teammember) {
         var itemtype = teammember["itemtype"];
         var items_id = teammember["items_id"];

         if (itemtype === 'User') {
            var user_img = null;
            $.ajax({
               url: (self.ajax_root + "../../../ajax/getUserPicture.php"),
               async: false,
               data: {
                  users_id: [items_id],
                  size: 30,
                  allow_blank: true,
               },
               contentType: 'application/json',
               dataType: 'json'
            }).done(function (data) {
               if (data[items_id] !== undefined) {
                  user_img = data[items_id];
               }
            });
            if (user_img !== null) {
               return "<span>" + user_img + "</span>";
            }
         }
         return '';
      };

      var updateMessageCounter = function() {
         var count = $("#inbox-dropdown-messages li").length;
         $('#inbox-dropdown .inbox-counter').first().html(count + " " + __("Messages", 'inbox'));
      };

      var getMessageIconClassForItemtype = function(itemtype) {
         switch (itemtype) {
            case 'Ticket':
               return 'fa-ticket-alt';
            case 'Change':
               return 'fa-exchange-alt';
            case 'Problem':
               return 'fa-exclamation-triangle';
            default:
               return 'fa-envelope';
         }
      };

      var getMessages = function(params, success, error) {
         $.ajax({
            method: 'GET',
            url: (self.ajax_root + "getMessages.php"),
            success: function(data, textStatus, jqHXR) {
               if (success !== undefined && typeof success === "function") {
                  success(data, textStatus, jqHXR);
               }
            },
            error: function(jqXHR, textStatus, errorThrown) {
               if (error !== undefined && typeof error === "function") {
                  error(jqXHR, textStatus, errorThrown);
               }
            }
         });
      };

      this.showMessageDropdown = function(caller, messages) {
         if (self.inboxDropdown === null) {
            $("<div id='inbox-dropdown'></div>").appendTo('body');
            self.inboxDropdown = $("#inbox-dropdown");
            $("<span class='bold'>Inbox<span class='inbox-counter right'>0 Messages</span></span><hr><ul id='inbox-dropdown-messages'></ul>").appendTo(self.inboxDropdown);
            var inboxURL = CFG_GLPI.root_doc + "/plugins/inbox/front/inbox.php";
            $("<hr class='faint'><a href='" + inboxURL + "'>View all messages</a>").appendTo(self.inboxDropdown);

            if (messages.length === 0) {
               $("#inbox-dropdown-messages").html("<li>" + __('No unread messages', 'inbox') + "</li>");
            } else {
               $("#inbox-dropdown-messages").empty();
               $.each(messages, function(ind, message) {
                  var msgClasses = 'message';
                  if (message.date_read === null) {
                     msgClasses += ' unread';
                  }
                  $("<li class='" + msgClasses + "'><div class='bold'>" + message.subject.substring(0, 50) + "</div><div>" + message.message.substring(0, 100) + "</div></li>").appendTo("#inbox-dropdown-messages");
               });
            }
            updateMessageCounter();
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

      this.showInbox = function(containerID) {
         getMessages({}, function(data) {
            var container = $("#"+containerID);
            var markAllAsRead = $("<a href='#'>" + __('Mark all as read', 'inbox') + "</a>").appendTo(container);
            markAllAsRead.on('click', function() {
               $.ajax({
                  url: (self.ajax_root + "readMessage.php")
               });
            });

            var messageList = $("<ul id='inbox-table'></ul>").appendTo(container);
            $.each(data, function(ind, message) {
               var icon = "<i class='fas fa-2x " + getMessageIconClassForItemtype(message.itemtype) + "'/>";
               if (message._link !== undefined) {
                  icon = "<a href='" + message._link + "'>" + icon + "</a>";
               }
               var msgSubject = "<span class='message-title' title='" + message.subject.replace(/'/g, "&#39;") + "'>" + message.subject + "</span>";
               var msgBody = "<span title='" + message.message.replace(/'/g, "&#39;") + "'>" + message.message + "</span>";
               var msgShort = msgSubject + "<br>" + msgBody;
               var imgUser = "<span class='user-badge'>" + getTeamBadge({
                  itemtype: 'User',
                  items_id: message.users_id_sender
               }) + "</span>";
               $("<li class='inbox-message'><span>" + icon + "</span><span class='message-short'>" + msgShort + "</span><span class='message-dates'>" + imgUser + message.date_sent + "</span>").appendTo(messageList);
            });
         }, function() {
            // Display error
            var container = $("#"+containerID);
            $("<div class='alert alert-danger'>" + __('Failed to retrieve messages', 'inbox') + "</div>").appendTo(container);
         });
      };

      this.showMessageConversation = function() {

      };

      this.showChatPopup = function(caller) {

      };

      this.init = function() {
         self.ajax_root = CFG_GLPI.root_doc + "/plugins/inbox/ajax/";
         getMessages({limit: 10}, function(data) {
            self.messages = data;
            var iconStack = "<i class='fas fa-inbox'/>";
            var unreadCount = 0;
            $.each(self.messages, function(ind, message) {
               if (message.date_read === null) {
                  unreadCount++;
               }
            });
            if (unreadCount > 0) {
               iconStack = "<span class='fa-stack'>" +
                  "<i class='fas fa-inbox fa-stack-1x'/>" +
                  "<i class='fas fa-circle fa-stack-half inbox-unread-notif'/>" +
                  "</span>";
            }
            $("<a id='inbox-btn' href='#' title='" + __('View inbox', 'inbox') + "'>" + iconStack + "</a>").insertAfter("#menu_all_button");
            $("#inbox-btn").on('click', function() {
               var caller = this;
               self.showMessageDropdown(caller, self.messages);
            });
         });

         // Show inbox if the expected element is on the page
         if ($("#glpi-inbox").length > 0) {
            self.showInbox("glpi-inbox");
         }
      };
   };
})();

$(document).ready(function() {
   var inbox = new window.inboxPlugin();
   inbox.init();
});