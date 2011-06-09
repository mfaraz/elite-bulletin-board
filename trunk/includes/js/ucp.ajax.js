/**
 * ucp.ajax.js
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 6/4/2011
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
				ucp.clearTemplateCache(); //clear the cache
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
				ucp.clearTemplateCache(); //clear the cache
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
					ucp.clearTemplateCache(); //clear the cache
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
					ucp.clearTemplateCache(); //clear the cache
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
					ucp.clearTemplateCache(); //clear the cache
					ucp.partial('quicktools/ucp.php?mode=avatar');
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
				ucp.clearTemplateCache(); //clear the cache
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END saveAvatar

		/**
		 * deletes the selected attachment.
		 * @version 6/5/2011
		*/
		$('#deleteAttachment').live('click', function() {

			//setup confirmation message.
			$('#confirmMsg').html(lang.cfmDel);

			//setup buttons
			var btnConf = {};
			 btnConf[lang.Yes] = function() {
				//call .ajax to call server.
				$.ajax({
					method: "get", url: "quicktools/ucp.php?mode=deleteAttachment", data: "id=" + $(this).attr("title"),
					success: function(html){
						//add results to our container.
						$("#notifyContainer").html(html);

						//see what message to display.
						var type = $('#output').text();
						var message = $('#message').text();

						FormResults(type, message); //display message.
						ucp.clearTemplateCache(); //clear the cache
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
		 * @version 6/5/2011
		*/
		$('#deleteSubscription').live('click', function() {

			//setup confirmation message.
			$('#confirmMsg').html(lang.cfmDel);

			//setup buttons
			var btnConf = {};
			 btnConf[lang.Yes] = function() {
				//call .ajax to call server.
				$.ajax({
					method: "get", url: "quicktools/ucp.php?mode=deleteAttachment", data: "del=" + $(this).attr("title"),
					success: function(html){
						//add results to our container.
						$("#notifyContainer").html(html);

						//see what message to display.
						var type = $('#output').text();
						var message = $('#message').text();

						FormResults(type, message); //display message.
						ucp.clearTemplateCache(); //clear the cache
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
				ucp.clearTemplateCache(); //clear the cache
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
				ucp.clearTemplateCache(); //clear the cache

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

      });//END .sammy

	//our default page.
	ucp.run('#/');
 })(jQuery);