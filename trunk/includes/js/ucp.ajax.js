/**
 * ucp.ajax.js
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 6/23/2011
*/

$(document).ready(function() {

	//global loading message.
	$('#loadingBackGround').hide()  // hide it initially
	.ajaxStart(function() {
		$(this).show();
    }) //END .ajaxStart
    .ajaxStop(function() {
       	$(this).hide();
	}); //END .ajaxStart


    var ucp = $.sammy('#ucpdata', function() {

		//call any plugins here.
		//this.use(Sammy.Title);

		//ucp dashboard
		this.get('#/', function() {
          this.partial('quicktools/ucp.php');
		  //$('#usrInfo').hide();
		  //$('#ucpMenu').show();
        });

		//User's profile
		this.get('#/:user', function() {
          this.partial('quicktools/ucp.php?user=' + this.params['user']);
		  //$('#ucpMenu').hide();
		  //$('#usrInfo').show();
        });

		//get the page based on what the user defined.
		this.get('#/ucp/:mode', function() {
			//see if the page exists.
			this.partial('quicktools/ucp.php?mode=' + this.params['mode']);
			this.title(this.params['mode']);
			//window.title = this.params['mode'];			
        });

		//PM Inbox
		this.get('#/PM/', function() {
          this.partial('quicktools/quicktools/pm.ajax.php');
        });

		//Send out a new PM.
		this.get('#/PM/compose', function() {
			//see if the page exists.
			this.partial('quicktools/pm.ajax.php?action=newMsg');
			//window.title = this.params['mode'];
        });

		//Send out a new PM to a define user.
		this.get('#/PM/compose/:user', function() {
			//see if the page exists.
			this.partial('quicktools/pm.ajax.php?action=newMsg&user=' + this.params['user']);
			//window.title = this.params['mode'];
        });

		//Send out a new PM to a define user.
		this.get('#/PM/replyPM/:id', function() {
			//see if the page exists.
			this.partial('quicktools/pm.ajax.php?action=replyMsg&id=' + this.params['id']);
			this.title(this.params['mode']);
			//window.title = this.params['mode'];
        });

		//PM folder selection.
		this.get('#/PM/:folder', function() {
			//see if the page exists.
			this.partial('quicktools/pm.ajax.php?folder=' + this.params['folder']);
			//window.title = this.params['mode'];
        });

		//
		//POST Events
		//

		/**
		 * Saves User's Profile information.
		 * @version 6/4/2011
		*/
		this.post('#/ucp/saveProfile', function(context) {

			//call .ajax to call server.
			$.post("quicktools/ucp.php?mode=saveProfile", $('#UpdateProfile').serialize(), function(html){
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.
				$('#pwd').val(''); //clear the password field.

				//perform only if its successful.
				if(type == "success") {
					ucp.clearTemplateCache(); //clear the cache
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END saveProfile

		/**
		 * Saves User's signature.
		 * @version 6/4/2011
		*/
		this.post('#/ucp/saveSignature', function(context) {

			//call .ajax to call server.
			$.post("quicktools/ucp.php?mode=saveSignature", $('#UpdateSignature').serialize(), function(html){
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					ucp.clearTemplateCache(); //clear the cache
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END saveSignature

		/**
		 * Join user to defined group.
		 * @version 6/4/2011
		*/
		$('#JoinGroup').live('click', function() {
			//call .ajax to call server.
			$.ajax({
				method: "get", url: "quicktools/ucp.php?mode=joinGroup", data: "id=" + $(this).attr("title"),
				success: function(html){
					//add results to our container.
					$("#notifyContainer").html(html);

					//see what message to display.
					var type = $('#output').text();
					var message = $('#message').text();

					FormResults(type, message); //display message.

					//perform only if its successful.
					if(type == "success") {
						ucp.clearTemplateCache(); //clear the cache
					}
				},
				error: function(xhr, tStat, err){
					var msg = lang.jsError + ": ";
					FormResults("error", msg + xhr.status + " " + xhr.statusText);
				}
			}); //END $.ajax(
		}); //END JoinGroup
		
		/**
		 * Un-Join user to defined group.
		 * @version 6/4/2011
		*/
		$('#UnjoinGroup').live('click', function() {
			//call .ajax to call server.
			$.ajax({
				method: "get", url: "quicktools/ucp.php?mode=unjoinGroup", data: "id=" + $(this).attr("title"),
				success: function(html){
					//add results to our container.
					$("#notifyContainer").html(html);

					//see what message to display.
					var type = $('#output').text();
					var message = $('#message').text();

					FormResults(type, message); //display message.

					//perform only if its successful.
					if(type == "success") {
						ucp.clearTemplateCache(); //clear the cache
					}
				},
				error: function(xhr, tStat, err){
					var msg = lang.jsError + ": ";
					FormResults("error", msg + xhr.status + " " + xhr.statusText);
				}
			}); //END $.ajax(
		}); //END UnjoinGroup

		/**
		 * Clears user's avatar.
		 * @version 6/4/2011
		*/
		$('#clearAvatar').live('click', function() {
			//call .ajax to call server.
			$.ajax({
				method: "get", url: "quicktools/ucp.php?mode=clearavatar",
				success: function(html){
					//add results to our container.
					$("#notifyContainer").html(html);

					//see what message to display.
					var type = $('#output').text();
					var message = $('#message').text();

					FormResults(type, message); //display message.
					
					//perform only if its successful.
					if(type == "success") {
						ucp.clearTemplateCache(); //clear the cache
						ucp.partial('quicktools/ucp.php?mode=avatar');
					}
				},
				error: function(xhr, tStat, err){
					var msg = lang.jsError + ": ";
					FormResults("error", msg + xhr.status + " " + xhr.statusText);
				}
			}); //END $.ajax(
		}); //END clearAvatar

		/**
		 * Saves user's avatar choice.
		 * @version 6/4/2011
		*/
		this.post('#/ucp/saveAvatar', function(context) {

			//call .ajax to call server.
			$.post("quicktools/ucp.php?mode=saveAvatar", $('#updateAvatar').serialize(), function(html){
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					ucp.clearTemplateCache(); //clear the cache
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END saveAvatar

		/**
		 * deletes the selected attachment.
		 * @version 6/23/2011
		*/
		$('#deleteAttachment').live('click', function() {

			var attachID = $(this).attr("title");

			//setup confirmation message.
			$('#confirmMsg').html(lang.cfmDel);

			//setup buttons
			var btnConf = {};
			 btnConf[lang.Yes] = function() {
				//call .ajax to call server.
				$.ajax({
					method: "get", url: "quicktools/ucp.php?mode=deleteAttachment", data: "id=" + attachID,
					success: function(html){
						//add results to our container.
						$("#notifyContainer").html(html);

						//see what message to display.
						var type = $('#output').text();
						var message = $('#message').text();

						FormResults(type, message); //display message.

						//perform only if its successful.
						if(type == "success") {
							ucp.clearTemplateCache(); //clear the cache
						}
					},
					error: function(xhr, tStat, err){
						var msg = lang.jsError + ": ";
						FormResults("error", msg + xhr.status + " " + xhr.statusText);
					}
				}); //END $.ajax(
			};
			btnConf[lang.No] = function() {
				$(this).dialog('close');
			};

			//add buttons to dialog.
			$("#confirmDlg").dialog({
			  title: lang.confDel,
			  buttons : btnConf,
			  open: function(event, ui) {
				  $(".ui-dialog-titlebar-close", ui.dialog).hide();
			  }
			});

			//show dialog.
			$("#confirmDlg").dialog('open');

		}); //END deleteAttachment

		/**
		 * Deletes the selected topic subscription.
		 * @version 6/23/2011
		*/
		$('#deleteSubscription').live('click', function() {

			var del = $(this).attr("title");

			//setup confirmation message.
			$('#confirmMsg').html(lang.cfmDel);

			//setup buttons
			var btnConf = {};
			 btnConf[lang.Yes] = function() {
				//call .ajax to call server.
				$.ajax({
					method: "get", url: "quicktools/ucp.php?mode=deleteAttachment", data: "del=" + del,
					success: function(html){
						//add results to our container.
						$("#notifyContainer").html(html);

						//see what message to display.
						var type = $('#output').text();
						var message = $('#message').text();

						FormResults(type, message); //display message.

						//perform only if its successful.
						if(type == "success") {
							ucp.clearTemplateCache(); //clear the cache
						}
					},
					error: function(xhr, tStat, err){
						var msg = lang.jsError + ": ";
						FormResults("error", msg + xhr.status + " " + xhr.statusText);
					}
				}); //END $.ajax(
			};
			btnConf[lang.No] = function() {
				$(this).dialog('close');
			};

			//add buttons to dialog.
			$("#confirmDlg").dialog({
			  title: lang.confDel,
			  buttons : btnConf,
			  open: function(event, ui) {
				  $(".ui-dialog-titlebar-close", ui.dialog).hide();
			  }
			});

			//show dialog.
			$("#confirmDlg").dialog('open');

		}); //END deleteSubscription

		/**
		 * Saves user's new email address.
		 * @version 6/5/2011
		*/
		this.post('#/ucp/updateEmail', function(context) {

			//call .ajax to call server.
			$.post("quicktools/ucp.php?mode=updateEmail", $('#updateEmail').serialize(), function(html){
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					ucp.clearTemplateCache(); //clear the cache
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END updateEmail

		/**
		 * Saves user's new password.
		 * @version 6/5/2011
		*/
		this.post('#/ucp/updatePassword', function(context) {

			//call .ajax to call server.
			$.post("quicktools/ucp.php?mode=updatePassword", $('#updatePassword').serialize(), function(html){
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//let user they need to re-login to finalize password change.
				if(type == "success") {
					//setup confirmation message.
					$('#confirmMsg').html(lang.logoffNotice);

					//setup buttons
					var btnConf = {};
					 btnConf[lang.Ok] = function() {
						//log user out.
						gotoUrl('logout.php');
					};

					//add buttons to dialog.
					$("#confirmDlg").dialog({
					  title: lang.reloginTitle,
					  buttons : btnConf,
					  open: function(event, ui) {
						  $(".ui-dialog-titlebar-close", ui.dialog).hide();
					  }
					});

					//show dialog.
					$("#confirmDlg").dialog('open');

				}

			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END updateEmail

		/**
		 * Deletes PM message.
		 * @version 6/22/2011
		*/
		function pmDelete(pmID){

			//setup confirmation message.
			$('#confirmMsg').html(lang.cfmDel);

			//setup buttons
			var btnConf = {};
			 btnConf[lang.Yes] = function() {
				//call .ajax to call server.
				$.ajax({
					method: "get", url: "quicktools/pm.ajax.php?action=deleteMsg", data: "id=" + pmID,
					success: function(html){

						//add results to our container.
						$("#notifyContainer").html(html);

						//see what message to display.
						var type = $('#output').text();
						var message = $('#message').text();

						FormResults(type, message); //display message.

						//close dialog.
						$("#confirmDlg").dialog('close');

						//perform only if its successful.
						if(type == "success") {
							setTimeout("window.location.reload()", 2000); //reload after 2 seconds.
						}
					},
					error: function(xhr, tStat, err){
						var msg = lang.jsError + ": ";
						FormResults("error", msg + xhr.status + " " + xhr.statusText);
					}
				}); //END $.ajax(
			};
			btnConf[lang.No] = function() {
				$(this).dialog('close');
			};

			//add buttons to dialog.
			$("#confirmDlg").dialog({
			  title: lang.confDel,
			  buttons : btnConf,
			  open: function(event, ui) {
				  $(".ui-dialog-titlebar-close", ui.dialog).hide();
			  }
			});

			//show dialog.
			$("#confirmDlg").dialog('open');

		}

		//deletes all selected records.
		$('#cmdDeleteSelected').live('click', function(e){

			e.preventDefault(); //we don't want to leave this page.

			var deleted = 0;
			var count = 0;

			//loop through checked messages.
			$('.PM-MsgList input').each(function () {
				if (this.checked) {
					$.ajax({
						method: "get", url: "quicktools/pm.ajax.php?action=deleteMsg", data: "id=" + $(this).attr("title"),
						success: function(){
							//don't really do anything.
						}
					}); //END $.ajax(
					deleted = deleted + 1;
				}

				count = count + 1;
			});

			var deleteRes = deleted +" of " + count + " deleted. Reloading in 3 seconds.";

			//setup confirmation message.
			$('#confirmMsg').html(deleteRes);

			//add buttons to dialog.
			$("#confirmDlg").dialog({
			  title: lang.genSuccess,
			  open: function(event, ui) {
				  $(".ui-dialog-titlebar-close", ui.dialog).hide();
			  }
			});

			//show dialog.
			$("#confirmDlg").dialog('open');

			//perform only if successful.
			if (deleted > 0){
				setTimeout("window.location.reload()", 3000); //reload after 3 seconds.
			}

		});

		//link click on main inbox.
		$('#lnkDelete').live('click', function(e) {
			e.preventDefault(); //we don't want to leave this page.

			pmDelete($(this).attr("title"));

		});

		//button click on message.
		$('#PMcmd-Delete').live('click', function() {

			pmDelete($(this).attr("title"));

		}); //END PMcmd-Delete

		/**
		 * Archive PM message.
		 * @version 6/22/2011
		*/
		$('#PMcmd-Move').live('click', function() {

			var pmID = $(this).attr("title");

			//setup confirmation message.
			$('#confirmMsg').html(lang.archiveConf);

			//setup buttons
			var btnConf = {};
			 btnConf[lang.Yes] = function() {
				//call .ajax to call server.
				$.ajax({
					method: "get", url: "quicktools/pm.ajax.php?action=moveMsg", data: "id=" + pmID,
					success: function(html){
						//add results to our container.
						$("#notifyContainer").html(html);

						//see what message to display.
						var type = $('#output').text();
						var message = $('#message').text();

						FormResults(type, message); //display message.

						//close dialog.
						$("#confirmDlg").dialog('close');

						//perform only if its successful.
						if(type == "success") {
							setTimeout("window.location.reload()", 2000); //reload after 2 seconds.
						}
					},
					error: function(xhr, tStat, err){
						var msg = lang.jsError + ": ";
						FormResults("error", msg + xhr.status + " " + xhr.statusText);
					}
				}); //END $.ajax(
			};
			btnConf[lang.No] = function() {
				$(this).dialog('close');
			};

			//add buttons to dialog.
			$("#confirmDlg").dialog({
			  title: lang.confDel,
			  buttons : btnConf,
			  open: function(event, ui) {
				  $(".ui-dialog-titlebar-close", ui.dialog).hide();
			  }
			});

			//show dialog.
			$("#confirmDlg").dialog('open');

		}); //END PMcmd-Move (PMcmd-Reply)

		/**
		 * Post a reply to PM Message.
		 * @version 6/9/2011
		*/
		$('#PMcmd-Reply').live('click', function() {

			//setup confirmation message.
			gotoUrl('Profile.php#/PM/replyPM/'+ $(this).attr("title"));

		}); //END PMcmd-Reply

		/**
		 * Send User a PM Message.
		 * @version 6/22/2011
		*/
		this.post('#/PM-Msg/sendPM', function(context) {

			//call .ajax to call server.
			$.post("quicktools/pm.ajax.php?action=sendPM", $('#sendPM').serialize(), function(html){
				
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//clear our form if its successful.
				if(type == "success") {
					//clear form.
					$('#sendto').val('');
					$('#subject').val('');
					$('#pmMsg').val('');
					ucp.clearTemplateCache(); //clear the cache
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END sendPM

		/**
		 * Send User a PM Reply.
		 * @version 6/9/2011
		*/
		this.post('#/PM-Msg/sendPMReply', function(context) {

			//call .ajax to call server.
			$.post("quicktools/pm.ajax.php?action=sendPMReply", $('#sendPMReply').serialize(), function(html){

				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//clear our form if its successful.
				if(type == "success") {
					//clear form.
					$('#sendto').val('');
					$('#subject').val('');
					$('#pmMsg').val('');
					ucp.clearTemplateCache(); //clear the cache
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END sendPM

      });//END .sammy

	//our default page.
	ucp.run('#/');
 })(jQuery);