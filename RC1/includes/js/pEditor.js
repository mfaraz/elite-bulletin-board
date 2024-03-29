/**
Filename: pEditor.js
Last Modified: 6/9/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

$(document).ready(function()	{
	//add jquery.markitup plugin to textarea.
	$('#replyBody').markItUp(myBbcodeSettings);

	//smile BBCode Handler.
	$('#emoticons a').click(function(e) {
		e.preventDefault(); //we don't want to leave this page.
        emoticon = $(this).attr("title");
        $.markItUp( { replaceWith:emoticon } );
    });

	// And you can add/remove markItUp! whenever you want
	$('.toggle').live("click", function() {
		if ($("#replyBody.markItUpEditor").length === 1) {
 			$("#replyBody").markItUpRemove();
 			$("#emoticons").hide();
			$("span", this).html(lang['enableRTF']);
		} else {
			$('#replyBody').markItUp(myBbcodeSettings);
			$("#emoticons").show();
			$("span", this).html(lang['disableRTF']);
		}
 		return false;
	});
});