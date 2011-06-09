/**
 * pm.ajax.js
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 4/13/2011
*/

$(document).ready(function() {
	//see what was clicked and then do something.
	$('ul#pmMessages li').click(function(e){
		e.preventDefault(); //we don't want to leave this page.

		var selectedID = $(this).attr("id"); //our PM ID.

		$.ajax({
			type: "get", url: "doc/" +selectedID + ".html", data: "",
			beforeSend: function(){
				$("#content").fadeOut(); //animation
				$("#loading").show("fast");
			}, //show loading just when link is clicked
			complete: function(){
				$("#content").fadeIn(); //animation
				$("#loading").hide("fast");
			}, //stop showing loading when the process is complete
			success: function(html){ //so, if data is retrieved, store it in html
				$("#content").html(html); //show the html inside .content div
			}
		}); //close $.ajax(
	});
});//END document.