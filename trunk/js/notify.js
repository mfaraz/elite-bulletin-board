/**
 * notify.js
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 5/31/2011
*/

function GrowlNotify() {
	//todo: get this working.
	//will get implemented in RC 2 release
}


/**
 * Displays results of AJAX calls.
 * @param type str "type of message (Error, Success, Notice)."
 * @param message str "The Message to display to the user."
 * @version 5/31/2011
*/
function FormResults(type, message) {
	$(function(){
		//see how we're rendering this notification.
		if (type == "success") {
			$.jnotify(message, 3000);
		} else {
			$.jnotify(message, type, 3000);
		}
	});
}
