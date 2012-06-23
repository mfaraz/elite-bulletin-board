/**
 * tEditor.js
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2013
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 06/22/2012
*/

$(document).ready(function()	{
	//add jquery.markitup plugin to textarea.
 	$('#body').markItUp(myBbcodeSettings);

	//smile BBCode Handler.
	$('#emoticons a').click(function(e) {
		e.preventDefault(); //we don't want to leave this page.
        emoticon = $(this).attr("title");
        $.markItUp( { replaceWith:emoticon } );
    });

	// And you can add/remove markItUp! whenever you want
	$('.toggle').click(function(e) {
		e.preventDefault(); //we don't want to leave this page.
		if ($("#body.markItUpEditor").length === 1) {
 			$("#body").markItUpRemove();
 			$("#emoticons").hide();
			$("span", this).html(lang['enableRTF']);
		} else {
			$('#body').markItUp(myBbcodeSettings);
			$("#emoticons").show();
			$("span", this).html(lang['disableRTF']);
		}
 		return false;
	});
});