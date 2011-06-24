/**
Filename: common.js
Last Modified: 6/11/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

/*
*displayMsg
*
*output message in HTML format.
*@param msg[string] - message to output.
*@param ele[string] - the element id to look for.
*@param icon[string] - used to see if user wants to use an icon or not.
*@return HTML-friendly message.
*
*/
function displayMsg(msg, ele, icon){
	obj = document.getElementById(ele);
	
	if (obj){
		//see what type of icon to use, if any.
		if(icon == "err"){
			obj.innerHTML = "<div id=\"error\"><img src=\"images/error.gif\" alt=\"Error\" />" + msg + "</div>";
		}else if(icon == "info"){
			obj.innerHTML = "<div id=\"info\"><img src=\"images/info.gif\" alt=\"Information\" />" + msg + "</div>";
		}else if(icon == "ok"){
			obj.innerHTML = "<div id=\"ok\"><img src=\"images/ok.gif\" alt=\"Ok\" />" + msg + "</div>";
        }else if(icon == "loading"){
			obj.innerHTML = "<div id=\"loading\"><img src=\"images/loading.gif\" alt=\"Loading...\" />" + msg + "</div>";
		}else if(icon == "none"){
			obj.innerHTML = "<div>" + msg + "</div>";
		}
	}else{
	    alert('No Object Found!');
	}
}

/**
 *clearMsg
 *
 *clear any messages made from previous function.
 *
 *@param ele[string] - the element id to look for.
 *
 *@return nulled html code.
 *
*/
function clearMsg(ele){
	obj = document.getElementById(ele);
	if (obj){
	   obj.innerHTML = "<div></div>";
	}else{
	    alert('No Object Found!');
	}
}

/**
 *gotoUrl
 *
 *Used to redirect a user to a location within the program's directory.
 *
 *@param addr[str] - the url to direct user to.
 *
*/
function gotoUrl(addr){
	window.location = addr;
}

/**
 *confirmDlg
 *
 *Used to display a confirmation dialog to the user.
 *
 *@param msg[str] - the message displayed to the user.
 *@param addr[str] - the url to direct user to.
 *
*/
function confirmDlg(msg, addr){
	if (confirm(msg)){
		gotoUrl(addr);
	}
}

$(document).ready(function(){

	//See if the Firebug Extension is used by an user visiting the site.
	//if so, let them know it should be turned off.
	if($.browser.mozilla){
		if (window.console && window.console.firebug) {
			/* firebug found! */
			$('#fbWarn').show();
			$('#fbWarnInfo').html(lang.fbWarning);
		} else {
			$("#fbWarn").hide();
		}
	} else {
		$("#fbWarn").hide();
	}

	//small loader
	$("#smloading").ajaxStart(function(){
		$(this).show();
 	});
	$("#smloading").ajaxStop(function(){
    	$(this).hide();
	});

	//check for similar topics on change.
	similarTopics();

	//load up group roster
	viewRoster();

	//live search event.
	liveSearch();

}); //END (document).ready

/**
 *
**/
function loadIframe(ele, src){
	$('#'+ ele).attr('src', src);
}

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

/**
 * similarTopics
 * AJAX call to find similar topics.
**/
function similarTopics(){
    	$('#topic').change(function() {
			//ensure we enter in data first.
			if($(this).val() != ""){
			
				//show panel.
				$('#div_notice').show();

				//call .ajax to call server.
				$.post("quicktools/relatedtopics.php", { topic: $(this).val()},function(html){
					$("#similar").html(html).removeClass("ui-state-error");
				}); //END $.ajax(

				//error handler.
				$("#similar").ajaxError(function(e, xhr){
					var msg = lang.jsError + ":<br />";
   					$(this).html(msg + xhr.status + " " + xhr.statusText).addClass("ui-state-error");
 				});
			
			}
		}); //END .change
}

//900,000 = 15 minutes
function updateOnline(){

	$("#online").load("quicktools/online.php", function(response, status, xhr) {
		if (status == "error") {
	    	var msg = lang.jsError + ": ";
	    	$("#online").html(msg + xhr.status + " " + xhr.statusText).addClass("ui-state-error");
	  	}
	}); //END $.load
}
//setInterval( "updateOnline()", 300000);