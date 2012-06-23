/**
 * common.js
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2013
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 06/22/2012
*/

/**
 * Used to redirect a user to a location within the program's directory.
 * @param addr[str] - the url to direct user to.
*/
function gotoUrl(addr){
	window.location = addr;
}

/**
 * Used to display a confirmation dialog to the user.
 * @param msg[str] - the message displayed to the user.
 * @param addr[str] - the url to direct user to.
*/
function confirmDlg(msg, addr){
	if (confirm(msg)){
		gotoUrl(addr);
	}
}

/**
 * Reload iframe element.
 * @param ele[str] the iframe element to capture
 * @param src[str] the URL to execute.
 * @version 06/01/12
**/
function loadIframe(ele, src){
	$('#'+ ele).attr('src', src);
}

$(document).ready(function(){

	//small loader
	$("#smloading").ajaxStart(function(){
		$(this).show();
 	});
	$("#smloading").ajaxStop(function(){
    	$(this).hide();
	});

	//load up group roster
	//viewRoster();

	//live search event.
	liveSearch();

}); //END (document).ready

/**
 * liveSearch
 * AJAX Call to perform live search.
**/
function liveSearch(){
	//live search event.
	$('#iSearch').live('click', function() {
		//call .ajax to call server.
		$.ajax({
        	method: "get", url: "quicktools/livesearch.php", data: "q=" + $('#searchKeyword').val(),
			beforeSend: function(xhr){
				$("#lsloading").show();
			},
			complete: function(xhr, tStat){
				$("#lsloading").hide();
			},
			success: function(html){
				$("#livesearch").show();
				$("#livesearch").html(html).removeClass("ui-state-error");
			},
			error: function(xhr, tStat, err){
				var msg = lang.jsError + ": ";
	    		$("#livesearch").html(msg + xhr.status + " " + xhr.statusText).addClass("ui-state-error");
			}
		}); //END $.ajax(
	});//END .live
}

/**
 * viewRoster
 * AJAX call to load Group Roster list.
**/
function viewRoster(){
	$('#viewGroupRoster').live('click', function() {
		//call .ajax to call server.
		$.ajax({
        	method: "get", url: "quicktools/viewgrouproster.php", data: "groupid=" + $(this).attr("title"),
			beforeSend: function(xhr){
				$("#smloading").show();
			},
			complete: function(xhr, tStat){
				$("#smloading").hide();
			},
			success: function(html){
				$("#groupRoster").show();
				$("#groupRoster").html(html).removeClass("ui-state-error");
			},
			error: function(xhr, tStat, err){
				var msg = lang.jsError + ": ";
	    		$("#groupRoster").html(msg + xhr.status + " " + xhr.statusText).addClass("ui-state-error");
			}
		}); //END $.ajax(
	}); //END .live
}

//900,000 = 15 minutes
/*function updateOnline(){

	$("#online").load("quicktools/online.php", function(response, status, xhr) {
		if (status == "error") {
	    	var msg = lang.jsError + ": ";
	    	$("#online").html(msg + xhr.status + " " + xhr.statusText).addClass("ui-state-error");
	  	}
	}); //END $.load
}*/
//setInterval( "updateOnline()", 300000);
