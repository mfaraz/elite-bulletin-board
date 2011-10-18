/**
Filename: jquery.jUploader.js
Date Modified: 2/9/2011
Version 1.0.1
Requirements: jQuery 1.4+ & jQuery UI 1.8+

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
**/

(function($) {
	$.jUploader = {version: '1.0.1'};
	$.fn.jUploader = function(config){
		
		config = $.extend({}, {
			LoadingCss: "ui-state-highlight",
			LoadingGfx: "ajax-loader.gif",
        	LoadingMsg: "Uploading File",
        	PendingCss: "uploadData",
        	PendingMsg: "Pending...",
        	SuccessCss: "ui-widget-content",
        	SuccessMsg: "File Uploaded.",
        	FailureCss: "ui-state-error",
        	FailureMsg: "Upload Failed.",
			buttonUpload: "#fileUpload",
			buttonClear: "#ClearList",
			buttonFileMgr: "#AttachList",
			uploadLimit: "5",
			uploadLimitMsg: "You have reached your upload limit."
		}, config);
		
		//setup some global variables.
		var counter = 0;
		var inputName = "attachment";
		
  		/**
  		 *Adds our files to the download list.
		**/
		$.jUploader.AddtoQueue = function(e){

			//make sure user hasn't reached their limit yet.
			if (counter < config.uploadLimit || config.uploadLimit == ""){
            	var loading = '';
            
				//make our buttons clickable.
				$('#frmButtons input').removeAttr("disabled");
				$(config.buttonFileMgr).attr("disabled", true);
				$(config.buttonClear).attr("disabled", false);

				//see if theres a loading graphic defined.
				if ($.trim(config.LoadingGfx) != ''){
					loading = '<img src="'+ config.LoadingGfx +'" alt="'+ config.LoadingMsg +'" title="'+ config.LoadingMsg +'" />';
				} else {
					loading = config.LoadingMsg;
				}
			
				//setup our file panel.
				var display = '<div class="'+ config.PendingCss +'" id="jupload_'+ counter +'_msg" title="jupload_'+ counter +'">' +
					'<div class="close">&nbsp;</div>' +
					'<span class="fname">'+ $(e).val() +'</span>' +
					'<span class="loader" style="display:none">'+ loading +'</span>' +
					'<div class="status">'+ config.PendingMsg +'</div></div>';
			
				//add to our div.
				$("#filePendings").append(display);
			
				//create a form for this file.
				jQUploader.appendForm();
				$(e).hide();
			} else {
				alert(config.uploadLimitMsg);
			}
		}
		
		/**
		 *Performs our file upload AJAX style.
		**/
		$(config.buttonUpload).click(function(){
			if (counter > 1){
				$('#frmButtons input').attr("disabled", true);
				$("#frmUpload form").each(function(){

					//make sure we have a file present to upload.
					e = $(this);
					var id = "#" + $(e).attr("id");
					var inputID = id + "_input";
					var inputVal = $(inputID).val();
					
					if (inputVal != ""){
						$(id + "_msg .status").text(config.LoadingMsg);
						$(id + "_msg").addClass(config.LoadingCss);
						$(id + "_msg .loader").show();
						$(id + "_msg .close").hide();

						//submit our form ajax-style.
						$(id).submit();
						$(id + "_iframe").load(function() {
							$(id + "_msg .loader").hide();
							results = $(this).contents().find("#output").text();
  							if (results == "success") {
  								$(id + "_msg").addClass(config.SuccessCss);
								results = config.SuccessMsg;
  							}else{
           						$(id + "_msg").addClass(config.FailureCss);
								results = config.FailureMsg;
  							}
  							results += '<br />' + $(this).contents().find("#message").text();
							$(id + "_msg .status").html(results);
							$(e).remove();
							$(config.buttonClear).removeAttr("disabled");
						});
					}
				});
			}
		});
		
		/**
		 *Removes a file form the queue.
		**/
		$(".close").live("click", function(){
			var id = "#" + $(this).parent().attr("title");
			$(id + "_iframe").remove();
			$(id).remove();
			$(id + "_msg").fadeOut("slow",function(){
				$(this).remove();
			});
			return false;
		});

        /**
		 *Removes a file form the database.
		**/
		$("#delete").live("click", function(){
			//call .ajax to call server.
			$.ajax({
		    	method: "get", url: "quicktools/filemanager.php", data: "mode=delete&id=" + $(this).attr("title"),
				beforeSend: function(xhr){
					//$("#smloading").show();
				},
				complete: function(xhr, tStat){
					//$("#smloading").hide();
				},
				success: function(html){
					$("#filelist").show();
					$("#filelist").html(html).removeClass("ui-state-error");
				},
				error: function(xhr, tStat, err){
					var msg = lang.jsError + ": ";
					$("#filelist").html(msg + xhr.status + " " + xhr.statusText).addClass("ui-state-error");
				}
			}); //END $.ajax(
		});

		/**
		 *Clears our file queue.
		**/
		$(config.buttonClear).click(function(){
			$("#filePendings").fadeOut("slow",function(){
				$("#filePendings").html("");
				$("#frmUpload").html("");
				$("#filelist").html("").removeClass("ui-state-error");
				counter = 0;
				jQUploader.appendForm();
				$('#frmButtons input').attr("disabled", true);
				$(config.buttonFileMgr).removeAttr("disabled");
				$(this).show();
			});
		});
		
		/**
		 *Displays uploaded files List.
		**/
		$(config.buttonFileMgr).click(function(){
			//call .ajax to call server.
			$.ajax({
		    	method: "get", url: "quicktools/filemanager.php", data: "",
				beforeSend: function(xhr){
					//$("#smloading").show();
				},
				complete: function(xhr, tStat){
					//$("#smloading").hide();
				},
				success: function(html){
					$("#filelist").show();
					$("#filelist").html(html).removeClass("ui-state-error");
				},
				error: function(xhr, tStat, err){
					var msg = lang.jsError + ": ";
					$("#filelist").html(msg + xhr.status + " " + xhr.statusText).addClass("ui-state-error");
				}
			}); //END $.ajax(		
		});


		
  		/**
		 *Create our plugin methods.
		**/
		var jQUploader = {
			init: function(e){
				var form = $(e).parents('form');
				jQUploader.formAction = $(form).attr('action');

				$(form).before(' \
    	            <div id="frmUpload"></div> \
					<div id="filePendings"></div> \
					<div id="frmButtons"></div> \
				');
				

				//add our two buttons to our div.
				$(config.buttonUpload+','+config.buttonClear+','+config.buttonFileMgr).appendTo('#frmButtons');
				$(config.buttonClear).attr("disabled", true);
				$(config.buttonFileMgr).attr("disabled", true);

				//see if our file input field has a name.
				if ( $(e).attr('name') != '' ){
					inputName = $(e).attr('name');
				}

				$(form).hide();
				$("#frmUpload").html(""); //prevents double upload controls.
				this.appendForm();
			},
			appendForm: function(){
				counter++; //increment the counter.

				//setup some values for our hidden IFRAME.
				var frmID = "jupload_" + counter;
				var iframeID = "jupload_" + counter + "_iframe";
				var inputID = "jupload_" + counter + "_input";
				var jQIframe = '<form method="post" id="'+ frmID +'" action="'+ jQUploader.formAction +'" enctype="multipart/form-data" target="'+ iframeID +'">' +
				'<input type="file" name="'+ inputName +'" id="'+ inputID +'" class="jupload" onchange="$.jUploader.AddtoQueue(this);" />' +
				'</form>' + 
				'<iframe id="'+ iframeID +'" name="'+ iframeID +'" src="about:blank" style="display:none"></iframe>';
				
				//add our hidden iframe to our div.
				$("#frmUpload").append(jQIframe);
			}
		}
		
		jQUploader.init(this);
		
		return this;
	}
})(jQuery);
