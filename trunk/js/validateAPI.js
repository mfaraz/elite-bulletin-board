/**
 * validateAPI.js
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2012
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 06/22/2012
*/

/**
 *validateUploadAPI
 *runs a check to see if the server has the correct extensions to use the uploader.
 *
*/
function validateUploadAPI(){

	//call .ajax to call server.	
	//
	//index.php/ajax/PrefCheck/
	$.ajax({
        method: "get", url:boardUrl+"index.php/ajax/PrefCheck/attachment",
		success: function(html){
			//see if user can upload files.
			if(html == "OK"){
				//user can upload file, load upload modal.
    			$('#DLG_upload').dialog('open');
			}else{
				$("#div_ErrMsg").show('blind', {}, 500);
				$("#ErrMsg").html(html);
			}
		},
		error: function(xhr, tStat, err){
			$("#div_ErrMsg").show('blind', {}, 500);
			$("#ErrMsg").html(xhr.status + " - " + xhr.statusText);
		}
	}); //END $.ajax(
}