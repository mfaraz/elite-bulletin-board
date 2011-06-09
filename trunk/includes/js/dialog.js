/**
Filename: dialog.js
Last Modified: 6/5/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

$(document).ready(function(){
	//help dialog.
	$('#help').dialog({
		autoOpen: false,
		modal: true,
		show: 'fade',
		closeOnEscape: true,
		height: "auto",
		width: 550,
		resizable: false,
		draggable: false,
		title: lang.dlgHelp
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
			$("#user").focus();
		},
		close: function(event, ui) {
			//clear form.
			$("#user").val("");
			$("#pass").val("");
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