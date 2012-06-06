/**
 * jquery.jUploader.js
 * @version 1.1 (06/03/2012)
 * @Requirements: jQuery 1.7 or newer
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * CREDIT
 * Concept based off of: http://pixelcone.com/fileuploader/
 * 
**/

(function($) {
	$.jUploader = {version: '1.1'};
	$.fn.jUploader = function(config){
		
		config = $.extend({}, {
			LoadingCss: "ui-state-highlight", //loading style.
			LoadingGfx: "ajax-loader.gif", //loading animation graphic.
        	LoadingMsg: "Uploading File", //loading text verbiage.
        	PendingCss: "uploadData", //pending style.
        	PendingMsg: "Pending...", //pending text verbiage.
        	SuccessCss: "ui-widget-content", //success color.
        	SuccessMsg: "File Uploaded.", //success text verbiage.
        	FailureCss: "ui-state-error", //error color.
        	FailureMsg: "Upload Failed.", //error text verbiage.
			buttonUpload: "#fileUpload", //upload button.
			buttonClear: "#ClearList", //clear list button.
			buttonFileMgr: "#AttachList", //file manager button.
			uploadLimit: "5", //limit of downloads. (0=unlimited)
			uploadLimitMsg: "You have reached your upload limit.", //limit text verbiage.
			deleteUrl: "delete.php", //url to delete file.
			confirmDeletMsg: "Are you sure you want to delete this file?", //delete confirmation verbiage.
			fileMgrUrl: "filemgr.php" //path to view attachment action.
		}, config);
		
		//see if user is using recommended version of jQuery.
		if (jQuery.fn.jquery < "1.7") {
			alert('jUploader requires jquery 1.7 or newer. Please upgrade your copy of jQuery.');
		}

		//setup some global variables.
		var counter = 0;
		var inputName = "attachment";
		
  		/**
  		 * Adds our files to the download list.
		**/
		$.jUploader.AddtoQueue = function(e){

			//make sure user hasn't reached their limit yet.
			if (counter <= config.uploadLimit || config.uploadLimit == 0){
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
		 * Performs our file upload AJAX style.
		**/
		$(document).on("click", config.buttonUpload, function(){
			var results;
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
							var json = $.parseJSON($(this).contents().text());
  							if (json.status == "success") {
  								$(id + "_msg").addClass(config.SuccessCss);
  								results = '<div class="delete" title="delete file" id="'+json.filename+'">&nbsp;</div>';
								results += config.SuccessMsg;
  							}else{
           						$(id + "_msg").addClass(config.FailureCss);
								results = config.FailureMsg;
  							}
  							results += '<br />' + json.msg;
							$(id + "_msg .status").html(results);
							$(e).remove();
							$(config.buttonClear).removeAttr("disabled");
						});
					}
				});
			}
		});
		
		/**
		 * Removes a file form the queue.
		**/
		$(document).on("click", '.close', function(){
			var id = "#" + $(this).parent().attr("title");
			$(id + "_iframe").remove();
			$(id).remove();
			$(id + "_msg").fadeOut("slow",function(){
				$(this).remove();
			});
			return false;
		});

        /**
		 * Removes a file from upload directory.
		**/
		$(document).on("click", '.delete', function(){
			if (confirm(config.confirmDeletMsg)) {			
				$(this).load(config.deleteUrl, { filename: $(this).attr('id') }, function(res, status, xhr) {
					if (status == "error") {
						var msg = "Sorry but there was an error: ";
						alert(msg + xhr.status + " " + xhr.statusText);
						return false;
					} else {
						var id = "#" + $(this).parent().parent().attr("id");
						$(id).remove();
						counter = counter-1; //remove from counter.
						
						//see if all downloads are deleted.
						if (counter == 1) {
							counter = 0; //reset counter
							jQUploader.appendForm(); //show form.
							$('#frmButtons input').attr("disabled", true);
							$(config.buttonFileMgr).removeAttr("disabled");
						}
						return false;
					}
				});
			}
		});

		/**
		 * Clears our file queue.
		**/
		$(document).on("click", config.buttonClear, function(){
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
		 * Displays uploaded files List.
		**/
		$(document).on("click", config.buttonFileMgr, function(){
			//call .ajax to call server.
			$.ajax({
		    	method: "get", url: config.fileMgrUrl, data: "",
				success: function(html){
					$("#filelist").show();
					$("#filelist").html(html).removeClass("ui-state-error");
				},
				error: function(xhr, tStat, err){
					var msg = "error: ";
					$("#filelist").html(msg + xhr.status + " " + xhr.statusText).addClass("ui-state-error");
				}
			}); //END $.ajax(		
		});

  		/**
		 * Create our plugin methods.
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

				//see if our file input field has a name.
				if ($(e).attr('name') != '' ){
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