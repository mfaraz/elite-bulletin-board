/**
Filename: validateAPI.js
Last Modified: 3/5/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

/**
 *validateSpellAPI
 *runs a check to see if the server has the correct extensions to use the spellchecker.
 *
*/
function validateSpellAPI(markItUp){

	//call .ajax to call server.
	$.ajax({
        method: "get", url: "quicktools/prefCheck.ajax.php", data: "action=spelling",
		success: function(html){
			//see if any spell engine is available.
			if(html == "OK"){
		        //start spell checking.
		        $(".loading").show();
		        $(markItUp.textarea).spellchecker({
					wordlist: {action: "after", element: ".markItUpFooter"},
					suggestBoxPosition: "above"
				}).spellchecker("check", function(result){
					// spell checker has finished checking words.
					$(".loading").hide();

					// if result is true then there are no badly spelt words.
					if (result) {
						alert(lang['nospellerrors']);
					}
				});
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

/**
 *validateUploadAPI
 *runs a check to see if the server has the correct extensions to use the uploader.
 *
*/
function validateUploadAPI(){

	//call .ajax to call server.
	$.ajax({
        method: "get", url: "quicktools/prefCheck.ajax.php", data: "action=attachment",
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