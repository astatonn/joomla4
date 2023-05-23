/* visit counter Joomla! Plugin
 * 
 * @author Joachim Schmidt - joachim.schmidt@jschmidt-systemberatung.de
 * @copyright Copyright (C) 2013 Joachim Schmidt. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * 
 * change activity: 
 1`   01.02.2015: Release V2.0.0 for Joomla 3.x
 *    09.08.2022: add geolocation server check
 */
function check_Server(num) 
{  
   switch (num) 
   { 		 
	   case 1:
	    var url = 'http://www.geoplugin.net/json.gp';

		fetch(url).then(function(response) {
		  return response.json();
		}).then(function(data) {
			
		  var resp = '  ' +  url + ' responded with: \n'; 	
		  if (data.geoplugin_status == 200)
		    success = 'success';
		  else
		    success = 'error';  
		  var status = '\n          status = ' + success; 	
		  var ip = '\n          ip = ' + data.geoplugin_request;	
		  var code = data.geoplugin_countryCode; 
		  var country = '\n          country = ' + data.geoplugin_countryName + ' (' + code + ')'; 
			 
		  alert(resp + status + ip + country); 
		  
		}).catch(function() {
		  alert("connection failed");
	    });  
	   break; 
	   
	   case 2:
	    var url = 'http://ip-api.com/json/'; 
	    fetch(url).then(function(response) {
		  return response.json();
		}).then(function(data) {
			
		  var resp = '  ' +  url + ' responded with: \n'; 	
		  var status = '\n          status = ' + data.status; 	
		  var ip = '\n          ip = ' + data.query;	
		  var code = data.countryCode; 
		  var country = '\n          country = ' + data.country + ' (' + code + ')'; 
			 
		  alert(resp + status + ip + country); 
		  
		}).catch(function() {
		  alert("connection failed");
	    });  

	   break;
	   
	   case 3:
	    var url = 'https://ip2c.org/77.55.235.217';
	    fetch(url)
	        .then((response) => response.text())
            .then((text) => {
          var result = text.split(";");
          var resp = '  https://ip2c.org  responded with: \n';
          if (result[0] == 1 )
           var success = 'success'; 
          else
            var success = 'error'; 	
		  var status = '\n          status = ' + success; 	
		  var ip = '\n          ip = 77.55.235.217';	
		  var code = result[1]; 
		  var country = '\n          country = ' + result[3] + ' (' + code + ')'; 
			 
		  alert(resp + status + ip + country); 
             
          }).catch(function() {
		  alert("connection failed");
	     });  
			 
	   break;  
	   
	   case 4: 
	   	var key =  document.getElementById('jform_params_server4').value;
	   	if ( key.length < 32 )
	   	{
		  alert ('  https://ipapi.co \n\n          Error: invalid or missing access key ' + key );
		  exit;
	    }
		var url = 'https://ipapi.co/json/?key=' + key; 
	    fetch(url)
	        .then((response) => response.json())
            .then((json) => {
			
		  var resp = '  https://ipapi.co  responded with: \n'; 

		  if (json.error )
		  {
			var status = '\n          status = error'; 
			var reason = '\n          reason = ' + json.reason;
			var msg = '\n          message = ' + json.message;
			alert (resp + status + reason + msg);	
		  }	
		  else
		  {	
		    var status = '\n          status = success'; 		  	
		    var ip = '\n          ip = ' + json.ip;	
		    var code = json.country_code; 
		    var country = '\n          country = ' + json.country_name + ' (' + code + ')';
		    alert(resp + status + ip + country); 
		  }
		  
		}).catch(function() {
		  alert("connection failed");
	     });  
	    break;
	    
	   default:
	     alert ('server invalid');
	     return;	      
   } 
   
return; 
  	
}
 
function jsvisit_getHTTPObject() {
	var xhr = false;
	if (window.XMLHttpRequest) {
		xhr = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		try {
			xhr = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
				xhr = false;
			}
		}
	}
	return xhr;
}

function jsvisit_setCookie(c_name, value, exdays) {
	var exdate = new Date();
	var domain = document.location.hostname;
	exdate.setDate(exdate.getDate() + exdays);
	var c_value = escape(value)
			+ ((exdays == null) ? "" : "; expires=" + exdate.toUTCString())
			+ "; path=/; domain=." + domain;
	document.cookie = c_name + "=" + c_value;
}

function jsvisit_getCookie(c_name) {
	var c_value = document.cookie;
	var c_start = c_value.indexOf(" " + c_name + "=");
	if (c_start == -1) {
		c_start = c_value.indexOf(c_name + "=");
	}
	if (c_start == -1) {
		c_value = null;
	} else {
		c_start = c_value.indexOf("=", c_start) + 1;
		var c_end = c_value.indexOf(";", c_start);
		if (c_end == -1) {
			c_end = c_value.length;
		}
		c_value = unescape(c_value.substring(c_start, c_end));
	}
	return c_value;
}

function jsvisitCountVisitors(session) {

	var request = jsvisit_getHTTPObject();

	if (request) {
		request.onreadystatechange = function() {
			jsvisit_parseResponse(request);
		};

		var sendRequest = true;
		if (session > 0) {
			// check if there is a cookie for this user
			var exdate = new Date();

			var visit_date = jsvisit_getCookie("visitortime");
			session = parseInt(session) * 60000;
			var check1 = exdate.getTime() - parseInt(visit_date);
			//var check2 = check1 / 60000;
			//alert(Math.round(check2) +"min");
			if (visit_date != null && visit_date != "") {
				if (exdate.getTime() < parseInt(visit_date) + parseInt(session))
					sendRequest = false;
			}

			value = exdate.getTime();
			jsvisit_setCookie('visitortime', value, 31);
		}

		// send request
		if (sendRequest) {
			//alert ("sending");
			var url = 'index.php?option=com_ajax&plugin=jsvisit_counter&group=system&format=raw';
			request.open("POST", url, true);
			request.send(null);
		}
	}
}

function jsvisit_parseResponse(request) {
	if (request.readyState == 4) {
		if (request.status == 200 || request.status == 304) {
			jsvisit_processReceived(request.responseText);
		}
	}
}

function jsvisit_processReceived(returnData) {
	//alert (returnData);
	return false;
}