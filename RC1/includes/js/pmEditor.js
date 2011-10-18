/**
Filename: pmEditor.js
Last Modified: 6/9/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

$(document).ready(function()	{
	//add jquery.markitup plugin to textarea.
	$('#pmMsg').markItUp(limitedBbcodeSettings);

	//smile BBCode Handler.
	$('#emoticons a').click(function(e) {
		e.preventDefault(); //we don't want to leave this page.
        emoticon = $(this).attr("title");
        $.markItUp( { replaceWith:emoticon } );
    });

	$('#showAllSmiles').click(function(e) {
		e.preventDefault(); //we don't want to leave this page.
        $('#moreSmiles').dialog('open');
    });

	// And you can add/remove markItUp! whenever you want
	$('.toggle').live("click", function(e) {
		e.preventDefault(); //we don't want to leave this page.

		if ($("#pmMsg.markItUpEditor").length === 1) {
 			$("#pmMsg").markItUpRemove();
 			$("#emoticons").hide();
			$("span", this).html(lang['enableRTF']);
		} else {
			$('#pmMsg').markItUp(limitedBbcodeSettings);
			$("#emoticons").show();
			$("span", this).html(lang['disableRTF']);
		}
 		return false;
	});
});