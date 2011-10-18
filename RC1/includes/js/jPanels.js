/*
======================================================================================
Filename: jPanels.js
Date Modified: 1/15/2011
Author: Ed Lomonaco
Version 2.1.3
Requirements: jQuery 1.4 & jQuery UI 1.8
Description: This file holds the jPanels plugin.
======================================================================================
*/

/*
=====================
@README:

::CALLING JPANELS::

JS CODE:
----------------------------------------------
$("#jPanels").jPanels();
$("#panel1").jPanels({ HeaderCss: "ui-widget-header", ContentCss: "ui-widget-content" });
$("#panel2").jPanels({ HeaderCss: "ui-widget-header", ContentCss: "ui-widget-content", IsCollapsible: false });
$("#panel3").jPanels({ HeaderCss: "ui-widget-header", ContentCss: "ui-state-error", IsCollasped: true });
$("#panel3").jPanels('hide');
$("#panel3").jPanels('show');
$("#panel3").jPanels('clearCookie');
----------------------------------------------
panel1 - element you want to make collapsible.
HeaderCSS - title CSS class.
ContentCss - content CSS class.
IsCollapsible - the option to make a panel toggle or not.
IsCollasped - the option to show or hide a panel by default.
hide - this method will allow you to hide the panel from a remote source(ex: button click, event callback, etc.)
show - this method will allow you to show the panel from a remote source(ex: button click, event callback, etc.)
clearCookie - clears the cookie made by jPanels

*to use system default values, use the first example.

::ADDING JPANELS TO WEB PAGE::

HTML CODE:
----------------------------------------------
<div id="panel1" title="Test Panel 1">
<div class="pContent">
Test Panel 1 Content<br />
<p>This panel has a button bar to your right.</p>
</div>
</div>
----------------------------------------------

::BUTTON BAR::

To add a button bar to the panel use the follow in the html code:

HTML CODE:
----------------------------------------------
<div class="buttonBar"><a href="#"><img src="Images/add.png" alt="Add" /></a></div>
----------------------------------------------
=====================
*/

(function ($) {

  //our plugin's methods
  var methods = {
    init: function (options) {
      //our configurations to customize the toggle menu.
      config = jQuery.extend({
        HeaderCss: "ui-widget-header",
        ContentCss: "ui-widget-content",
        IsCollapsible: true,
        IsCollasped: false
      }, options);

      //see if user is using recommended version of jQuery.
      if (jQuery.fn.jquery < "1.4") {
        $(this).html("Your using version <strong>" + jQuery().jquery + "</strong> of jQuery.; jPanels Requires version <strong>1.4 or newer</strong> of jQuery.").addClass('ui-state-error');

        //Remove the title attribute's to avoid the native tooltip from the browser.
        $(this).attr('title', '');
      } else {

        //make each call an independent call.
        this.each(function () {

          //setup a content container div.
          $(this).children().html("<div class='ContentContainer " + config.ContentCss + "'>" + $('.pContent', this).html() + "</div>");

          // get the title attribute for our panel.
          var title = $(this).attr('title');

          //Remove the title attribute's to avoid the native tooltip from the browser.
          $(this).attr('title', '');

          //see if the user wants to be able to toggle the panel.
          if (config.IsCollapsible == true) {
            //setup a title container div.
            $("<div class='TitleContainer " + config.HeaderCss + "' style='text-align:center;cursor:pointer;'><div>" + title + "</div></div>").prependTo($(this));

            //give the title container the ability to toggle the content container.
            $(".TitleContainer", $(this)).live("click", function () {
              $('.ContentContainer', $(this).parent()).fadeToggle("slow", "linear");
            });
          } else {
            //setup a title container div.
            $("<div class='TitleContainer " + config.HeaderCss + "'><div>" + title + "</div></div>").prependTo($(this));
          }

        }); //END this.each
      } //END version check.

      //this allows our plugin to be chainable (eg. $("#jPanels").jPanels().hide();).
      return this;
    },
    hide: function () {

      //make each call an independent call.
      this.each(function () {

        //hide content container.
        $('.ContentContainer', $(this).parent()).fadeOut("slow");
      }); //END this.each

      //this allows our plugin to be chainable (eg. $("#jPanels").jPanels().hide();).
      return this;
    },
    show: function () {

      //make each call an independent call.
      this.each(function () {

        //show content container.
        $('.ContentContainer', $(this).parent()).fadeIn("slow");
      }); //END this.each

      //this allows our plugin to be chainable (eg. $("#jPanels").jPanels().hide();).
      return this;
    }
  }; //END methods


  $.fn.jPanels = function (method) {

    // Method calling logic
    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } else if (typeof method === 'object' || !method) {
      return methods.init.apply(this, arguments);
    } else {
      alert('Method "' + method + '" does not exist on jQuery.jPanels');
    }

  }; //END .jPanels

})(jQuery);              //END jQuery.
