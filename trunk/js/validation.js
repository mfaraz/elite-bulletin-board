/**
Filename: validation.js
Last Modified: 2/17/10

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

/**
 *validateEmail
 *
 *Will validate email address entered by the user.
 *
 *@param ele[string] - the element id to look for.
 *@param outputEle[string] - the element id to use for outputting.
 *
 *@return[bool]
 *
*/
function validateEmail(ele, outputEle){

	var obj = document.getElementById(ele).value;
	regexEmail = /\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/i;

	//do a length test.
	if(obj == ""){
		displayMsg("Nothing was Entered.", outputEle, "err");
	}else if(obj.length > 255){
		displayMsg("The Email entered is too long.", outputEle, "err");
	}else{
		//do a regEx check.
		if (!regexEmail.test(obj)) {
			displayMsg("An Invalid Email was entered.", outputEle, "err");
		}else{
			//reset any messages created already.
			clearMsg(outputEle);
		}
	}
}

/**
 *validateUrl
 *
 *Will validate url address entered by the user.
 *
 *@param ele[string] - the element id to look for.
 *@param outputEle[string] - the element id to use for outputting.
 *
 *@return[bool]
 *
*/
function validateUrl(ele, outputEle){

	var obj = document.getElementById(ele).value;
	//validUrl = /#([^\'"=\]]|^)(http[s]?|ftp[s]?|gopher|irc){1}://([:a-z_\-\\./0-9%~]+){1}(\?[a-z=0-9\-_&;]*)?(\#[a-z0-9]+)?#mi/;

	//may need to keep these if above regex doesn't work.
	validHttp = /http:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
	//validHttps = /https:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;

	//see if anything was entered.
	if(obj == ""){
		displayMsg("Nothing was Entered.", outputEle, "err");
	}else{
		//do a regEx check.
		if (!validHttp.test(obj)) {
			displayMsg("An Invalid URL was entered.", outputEle, "err");
		}else{
			//reset any messages created already.
			clearMsg(outputEle);
		}
	}
}

/**
 *validateLength
 *
 *Will validate legth of data entered by the user.
 *
 *@param ele[string] - the element id to look for.
 *@param outputEle[string] - the element id to use for outputting.
 *@param maxVal[integer] - maximum value allowed for defined element.
 *
 *@return[bool]
 *
*/
function validateLength(ele, outputEle, maxVal){

	var obj = document.getElementById(ele).value;

    //do a check for empty string.
    if(obj == ""){
		displayMsg("Nothing was entered.", outputEle, "err");
    }else{
    	//do a regEx check.
		if (obj.length  > maxVal) {
			displayMsg("You have gone over the " + maxVal + " limit.", outputEle, "err");
		}else{
			//reset any messages created already.
			clearMsg(outputEle);
		}
	}
}

/**
 *validateNumeric
 *
 *Will validate to see if user entered a numeric value.
 *
 *@param ele[string] - the element id to look for.
 *@param outputEle[string] - the element id to use for outputting.
 *
 *@return[bool]
 *
*/
function validateNumeric(ele, outputEle){

	var obj = document.getElementById(ele).value;

    //do a check for empty string.
    if(obj == ""){
		displayMsg("Nothing was entered.", outputEle, "err");
    }else{
		//do a NaN() check.
		if (!isNaN(obj)) {
			displayMsg("Nonnumerical value entered.", outputEle, "err");
		}else{
			//reset any messages created already.
			clearMsg(outputEle);
		}
	}
}

/**
 *validateAlpha
 *
 *Will validate to see if user entered a valid entry.
 *
 *@param ele[string] - the element id to look for.
 *@param outputEle[string] - the element id to use for outputting.
 *
 *@return[bool]
 *
*/
function validateAlpha(ele, outputEle){

	var obj = document.getElementById(ele).value;
	regexAlpha = /^[a-zA-Z ]+$/;

    //do a check for empty string.
    if(obj == ""){
		displayMsg("Nothing was entered.", outputEle, "err");
    }else{
		//do a regEx check.
		if (!regexAlpha.test(obj)) {
			displayMsg("invalid value entered.", outputEle, "err");
		}else{
			//reset any messages created already.
			clearMsg(outputEle);
		}
	}
}

/**
 *validateAlphaNumeric
 *
 *Will validate to see if user entered a valid entry.
 *
 *@param ele[string] - the element id to look for.
 *@param outputEle[string] - the element id to use for outputting.
 *
 *@return[bool]
 *
*/
function validateAlphaNumeric(ele, outputEle){

	var obj = document.getElementById(ele).value;
	regexAlphaNumeric = /^[0-9a-zA-Z ]+$/;

    //do a check for empty string.
    if(obj == ""){
		displayMsg("Nothing was entered.", outputEle, "err");
    }else{
		//do a regEx check.
		if (!regexAlphaNumeric.test(obj)) {
			displayMsg("invalid value entered.", outputEle, "err");
		}else{
		    //clear message.
		    clearMsg(outputEle);
		}
	}
}

/**
 *validateNotNull
 *
 *Will see if form value was left untouched, used when no real requirement is needed.
 *
 *@param ele[string] - the element id to look for.
 *@param outputEle[string] - the element id to use for outputting.
 *
 *@return[bool]
 *
*/
function validateNotNull(ele, outputEle){
	var obj = document.getElementById(ele).value;
	
	//see if element is empty
	if(obj == ""){
		displayMsg("Nothing was Entered.", outputEle, "err");
	}else{
	    //clear message.
	    clearMsg(outputEle);
	}
}
