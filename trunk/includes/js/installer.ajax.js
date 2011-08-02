/**
 * installer.ajax.js
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 7/24/2011
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


    var ucp = $.sammy('#installData', function() {

		//installer home
		this.get('#/', function() {
          this.partial('install.php');
        });

		//fresh install
		this.get('#/install/sqldump', function() {
		  //call .ajax to call server.
			$.get("install.php?do=sqldump", function(html){
				
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					setTimeout("gotoUrl('index.php#/install/configureBoard')", 2000); //reload after 2 seconds.
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(

        });

		this.get('#/install/:step', function() {
          this.partial('install.php?do=' + this.params['step']);
        });

		//upgrade from v2.0/2.1
		this.get('#/upgrade-2/sqldump', function() {
		  //call .ajax to call server.
			$.get("upgrade-v2.php?do=sqldump", function(html){

				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					setTimeout("gotoUrl('index.php#/upgrade-2/convertAdministrator')", 2000); //reload after 2 seconds.
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(

        });

		this.get('#/upgrade-2/convertUserPwd', function() {
		  //call .ajax to call server.
			$.get("upgrade-v2.php?do=convertUserPwd", function(html){

				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					setTimeout("gotoUrl('index.php#/upgrade-2/finalize')", 2000); //reload after 2 seconds.
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(

        });

		this.get('#/upgrade-2/', function() {
			//see if the page exists.
			this.partial('upgrade-v2.php');
        });

		this.get('#/upgrade-2/:step', function() {
			//see if the page exists.
			this.partial('upgrade-v2.php?do=' + this.params['step']);
        });

		//upgrade from v3.0
		this.get('#/upgrade-3/sqldump', function() {
		  //call .ajax to call server.
			$.get("upgrade-v3.php?do=sqldump", function(html){

				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					setTimeout("gotoUrl('index.php#/upgrade-3/finalize')", 2000); //reload after 2 seconds.
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(

        });

		this.get('#/upgrade-3/', function() {
			//see if the page exists.
			this.partial('upgrade-v3.php');
        });

		this.get('#/upgrade-3/:step', function() {
          this.partial('upgrade-v3.php?do=' + this.params['step']);
        });

		//
		//POST Events
		//

		/**
		 * Create config.php file and also create tables and default data.
		 * @version 7/19/2011
		*/
		this.post('#/install/createConfig', function(context) {

			//call .ajax to call server.
			$.post("install.php?do=createConfig", $('#frmconfig').serialize(), function(html){
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					setTimeout("gotoUrl('index.php#/install/sqldump')", 2000); //reload after 2 seconds.
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END createConfig


		/**
		 * Setup board settings.
		 * @version 7/21/2011
		*/
		this.post('#/install/SaveConfig', function(context) {

			//call .ajax to call server.
			$.post("install.php?do=saveconfig", $('#configBoard').serialize(), function(html){
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					setTimeout("gotoUrl('index.php#/install/createAdministrator')", 2000); //reload after 2 seconds.
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END SaveConfig


		/**
		 * Create Administration account.
		 * @version 7/20/2011
		*/
		this.post('#/install/makeAdmin', function(context) {

			//call .ajax to call server.
			$.post("install.php?do=makeAdmin", $('#createAdmin').serialize(), function(html){
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					setTimeout("gotoUrl('index.php#/install/createCategory')", 2000); //reload after 2 seconds.
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END makeAdmin

		/**
		 * Create Category Board.
		 * @version 7/21/2011
		*/
		this.post('#/install/makeCategory', function(context) {

			//call .ajax to call server.
			$.post("install.php?do=makeBoards&type=1", $('#frmCategory').serialize(), function(html){
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					setTimeout("gotoUrl('index.php#/install/createBoard')", 2000); //reload after 2 seconds.
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END makeCategory

		/**
		 * Create Child board.
		 * @version 7/20/2011
		*/
		this.post('#/install/makeBoard', function(context) {

			//call .ajax to call server.
			$.post("install.php?do=makeBoards&type=2", $('#frmBoard').serialize(), function(html){
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					setTimeout("gotoUrl('index.php#/install/finalize')", 2000); //reload after 2 seconds.
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END makeBoard

		//
		// VERSION 2.0/2.1 UPGRADE
		//

		/**
		 * Create config.php file and also create/update tables and default data.
		 * @version 7/24/2011
		*/
		this.post('#/upgrade-2/createConfig', function(context) {

			//call .ajax to call server.
			$.post("upgrade-v2.php?do=createConfig", $('#frmconfig').serialize(), function(html){
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					setTimeout("gotoUrl('index.php#/upgrade-2/sqldump')", 2000); //reload after 2 seconds.
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END createConfig

		/**
		 * Saves Admin's new password.
		 * @version 7/22/2011
		*/
		this.post('#/upgrade-2/convertAdmin', function(context) {

			//call .ajax to call server.
			$.post("upgrade-v2.php?do=convertAdmin", $('#convertAdmin').serialize(), function(html){
								//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					setTimeout("gotoUrl('index.php#/upgrade-2/convertUserPwd')", 2000); //reload after 2 seconds.
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END convertAdmin

		//
		// VERSION 3 UPGRADE
		//

				/**
		 * Create config.php file and also create/update tables and default data.
		 * @version 7/24/2011
		*/
		this.post('#/upgrade-3/createConfig', function(context) {

			//call .ajax to call server.
			$.post("upgrade-v3.php?do=createConfig", $('#frmconfig').serialize(), function(html){
				//add results to our container.
				$("#notifyContainer").html(html);

				//see what message to display.
				var type = $('#output').text();
				var message = $('#message').text();

				FormResults(type, message); //display message.

				//perform only if its successful.
				if(type == "success") {
					setTimeout("gotoUrl('index.php#/upgrade-3/sqldump')", 2000); //reload after 2 seconds.
				}
			}).error(function(xhr, tStat, err) {
				var msg = lang.jsError + ": ";
				FormResults("error", msg + xhr.status + " " + xhr.statusText);
			}); //END $.ajax(
		}); //END createConfig

      });//END .sammy

	//our default page.
	ucp.run('#/');
 })(jQuery);
