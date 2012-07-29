/**
 * dialog.js
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2012
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 06/22/2012
*/
$(document).ready(function(){
	
	//modcp move topic.
	$('#mod_move').dialog({
		autoOpen: false,
		modal: true,
		closeOnEscape: true,
		height: 150,
		width: 250,
		show: 'fade',
		resizable: false,
		draggable: false,
		open: function(event, ui) {
			//focus on form.
			$("#movetopic").focus();
		},
		close: function(event, ui) {
			//clear form.
			$("#movetopic").val("");
		}
	});
	
	//login box.
	$('#login').dialog({
		autoOpen: false,
		modal: true,
		closeOnEscape: true,
		height: "auto",
		width: 375,
		show: 'fade',
		resizable: false,
		draggable: false,
		open: function(event, ui) {
			//focus on form.
			$("#username").focus();
		},
		close: function(event, ui) {
			//clear form.
			$("#user").val("");
			$("#pass").val("");
			$('#div_ErrMsg_QLogin').hide();
		}
	});

	//search box.
	var btnQSearch = {};
	 btnQSearch[lang.dlgAdvSearch] = function() {
         gotoUrl("Search.php");
     };
     btnQSearch[lang.dlgCancel] = function() {
         $(this).dialog('close');
     };

	$('#qSearch').dialog({
		autoOpen: false,
		modal: true,
		closeOnEscape: true,
		height: "auto",
		width: 375,
		show: 'fade',
		resizable: false,
		draggable: false,
		buttons: btnQSearch,
		open: function(event, ui) {
   			//focus on form.
			$("#searchKeyword").focus();
		},
		close: function(event, ui) {
			//clear form.
			$("#searchKeyword").val("");
			
			//clear resutls.
			$("#livesearch").html("");
		}
	});
	//smiles dialog.
	$('#moreSmiles').dialog({
		autoOpen: false,
		modal: false,
		closeOnEscape: true,
		height:350,
		width:550,
		resizable: true,
		draggable: true,
		title: lang.dlgSmilesList,
		open: function(event, ui){
			$(this).load('smiles.php');
		}// end open
	});
	
	//upload dialog.
		$('#DLG_upload').dialog({
		autoOpen: false,
		modal: true,
		closeOnEscape: true,
		height:400,
		width:450,
		resizable: true,
		draggable: true,
		title: lang.dlgUploadMgr,
		close: function(event, ui) {
   			$('#uploader').val('');
		}
	});

	//confirmation dialog.
	$("#confirmDlg").dialog({
      autoOpen: false,
      modal: true
    });


});//END funct.
