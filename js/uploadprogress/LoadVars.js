/**
 * Object LoadVars
 * 	Flash MX / MX2004 LoadVars Object porting for JavaScript
 *      [ with all its methods ]
 *
 * @author               Andrea Giammarchi
 * @date                 2005/08/09
 * @lastmod              2006/02/01 07:10 [fixed IE7 beta 2 problems]
 * @version              1.0e stable - tested with IE 5.0, 5.5, 6.0, 7 beta2 and FireFox 1.0.6, FireFox 1.5 and Opera 8, 8.5
 * @documentation	 http://livedocs.macromedia.com/flash/mx2004/main_7_2/wwhelp/wwhimpl/common/html/wwhelp.htm?context=Flash_MX_2004&file=00001408.html
 *			 NOTE:  documentation is the same of Flash MX 2004 documentation. Only onData method is different, because it's called
 *                       	only if there is an error and not everytime.
 */
function LoadVars() {
	
	/**
	 * Public method
         * 	add or modify headers
	 */
	function addRequestHeader() {
		var rqh = Array();
		if(arguments.length == 1 && typeof(arguments[0]) != 'string') {
			for(var a = 0; a < arguments[0].length; a++)
				rqh.push(arguments[0][a]);
		}
		else if(arguments.length == 2 && typeof(arguments[0]) == 'string' && typeof(arguments[1]) == 'string') {
			rqh.push(arguments[0]);
			rqh.push(arguments[1]);
		}
		if(rqh.length > 0 && (rqh.length % 2) == 0)
			__headers = rqh;
	};
	
	/**
	 * Public method
         * 	decode a string to internal values
	 */
	function decode(str) {
		var response = str.split('&');
		if(response.length > 0) {
			for(var a in response) {
				if(response[a] != '') {
					var pos = response[a].indexOf('=');
					var key = response[a].substr(0, pos);
					var value = response[a].substr((pos + 1), (response[a].length - pos));
					__self[key] = value;
				}
			}
		}
	};
	
	/**
	 * Public method
         * 	get loaded bytes
         *      NOTE: in Internet Explorer it's not the real value
	 */
	function getBytesLoaded() {
		return __getBytes('responseText.length');
	};
	
	/**
	 * Public method
         * 	get total bytes
         *      NOTE: in Internet Explorer it's not the real value
	 */
	function getBytesTotal() {
		return __getBytes('getResponseHeader("Content-Length")');
	};
	
	/**
	 * Public method
         * 	load a server page
         *      NOTE: there are some privacy policy with this method
	 */
	function load(url) {
		__self.loaded = false;
		var result = false;
		if(__bridge != null) {
			try {
				__bridge.open('GET', url, true);
				__bridge.send(null);
				__onprogress();
				result = true;
			}
			catch(evt) {
				__self.lastError = evt.toString();
				result = false;
			}
		}
		return result;
	};
	
	/**
	 * Public method
         * 	send something to the server
         *      NOTE: there are some privacy policy with this method
	 */
	function send(url, target, method) {
		__self.sendAndLoad(url, null, method);
	};
	
	/**
	 * Public method
         * 	load a server page after sending something
         *      NOTE: there are some privacy policy with this method
	 */
	function sendAndLoad(url, targetObject, method) {
		__self.loaded = false;
		var result = false;
		if(__bridge != null) {
			if(__self != targetObject)
				__delegate = targetObject;
			method = __metodChosed(method);
			var toserver = __toServer(result);
			var topage = '';
			for(var a in toserver)
				topage += a + '=' + escape(toserver[a]) + '&';
			topage = topage.substr(0, (topage.length - 1));
			result = false;
			try {
				if(method == 'GET') {
					if(topage.length > 0)
						url += '?' + topage;
					__bridge.open(method, url, true);
					__addHeaders();
					__bridge.send(null);
				}
				else {
					__bridge.open(method, url, true);
					__addHeaders();
					__bridge.setRequestHeader('Content-type', __self.contentType);
					__bridge.setRequestHeader('Content-length', topage.length);
					__bridge.setRequestHeader('Connection', 'close');
					__bridge.send(topage);
				}
				if(targetObject != null)
					__onprogress();
				result = true;
			}
			catch(evt) {
				__self.lastError = evt.toString();
				result = false;
			}
		}
		return result;
	};
	
	/**
	 * Public method
         * 	return text rappresentation of this object
	 */
	function toString() {
		return __toString(false);
	};
	
	/**
	 * Public unofficial method
         * 	get % progress while loading data
	 */
	function getProgress() {
		return __progress;
	};
	
	/** LIST OF ALL PRIVATE METHODS [ uncommented ] */
	function __unescapeString(str) {
		return unescape(str.split('+').join(' '));
	};
	function __resultRowManager(str) {
		var pos = str.indexOf('=');
		var key = str.substr(0, pos);
		var value = str.substr((pos + 1), (str.length - pos));
		if(__delegate == null)
			__self[key] = __unescapeString(value);
		else
			__delegate[key] = __unescapeString(value);
	};
	function __addHeaders() {
		var result = false;
		if(__headers.length > 0) {
			result = true;
			for(var a = 0; a < __headers.length; a+=2) {
				if(__headers[a].toLowerCase() == 'content-type')
					__self.contentType = __headers[(a+1)];
				else
					__bridge.setRequestHeader(__headers[a], __headers[(a+1)]);
			}
		}
		return result;
	};
	function __callOnLoad(s) {
		if(__delegate == null && typeof(__self.onLoad) != 'undefined')
			__self.onLoad(s);
		else if(__delegate != null && typeof(__delegate.onLoad) != 'undefined')
			__delegate.onLoad(s);
		__delegate = null;
	};
	function __onLoad() {
		var response = __bridge.responseText.split('&');
		if(response.length > 0) {
			for(var a in response) {
				if(response[a] != '')
					__resultRowManager(response[a]);
			}
		}
		__callOnLoad(true);
	};
	function __onData(s) {
		if(__delegate == null && typeof(__self.onData) != 'undefined')
			__self.onData(s);
		else if(__delegate != null && typeof(__delegate.onData) != 'undefined')
			__delegate.onData(s);
		__delegate = null;
	};
	function __onError() {
		__callOnLoad(false);
	};
	function __getBridge() {
		var result = null;
		if(typeof(XMLHttpRequest) != 'undefined')
			result = new XMLHttpRequest();
		else if(window.ActiveXObject) {
			var t = (navigator.userAgent.toLowerCase().indexOf('msie 5') != -1) ? 'Microsoft' : 'Msxml2';
			result = new ActiveXObject(t + '.XMLHTTP');
		}
		if(result == null)
			__self.lastError = 'This browser does not support XML request.';
		return result;
	};
	function __metodChosed(method) {
		if(method == null)
			method = 'POST';
		else {
			method = method.toUpperCase();
			if(method != 'POST' && method != 'GET')
				method = 'POST';
		}
		return method;
	};
	function __parseValue(v) {
		var tmpstr;
		switch(typeof(v))  {
			case 'function':
				tmpstr = '[type Function]';
				break;
			case 'object':
				switch(v.constructor) {
					case String:
					case Number:
						tmpstr = v;
						break;
					case Boolean:
						tmpstr = v == false ? 'false' : 'true';
						break;
					default:
						tmpstr = '[type Object]';
						break;
				}
				break;
			default:
				tmpstr = v;
				break;
		}
		return tmpstr;
	};
	function __toString(result) {
		var toserver = '';
		for(var a in __self) {
			if(a == 'getBytesTotal')
				result = true;
			else if(result == true)
				toserver += a + '=' + __parseValue(__self[a]) + '&';
		}
		return toserver.substr(0, (toserver.length-1));
	};
	function __toServer(result) {
		var toserver = Object();
		for(var a in __self) {
			if(a == 'getBytesTotal')
				result = true;
			else if(result == true)
				toserver[a] = __parseValue(__self[a]);
		}
		return toserver;
	};
	function __getBytes(what) {
		var result;
		if(typeof(window.XMLHttpRequest) == 'undefined' ||
		   typeof(__bridge.responseText) === 'unknown') {
			if(what == 'responseText.length')
				result = __bridge.readyState;
			else
				result = 4;
		}
		else {
			try {
				result = eval('__bridge.' + what);
			}
			catch(evt) {
				result = 0;
			}
		};
		return result;
	};
	function __onprogress() {
		__progress = 0;
		function __checkProgress() {
			if((window.XMLHttpRequest && __bridge.readyState >= 2 && typeof(__bridge.status) === 'number' && __bridge.status != 200)
			|| (__bridge.readyState == 4 && typeof(__bridge.statusText) === 'string' && __bridge.statusText.toUpperCase() !== 'OK')) {
				__self.lastError = __bridge.statusText;
				if(typeof(__bridge.statusText) === 'string' && __bridge.statusText != '')
					__onData(__bridge.responseText);
				else
					__onData('Error #' + __bridge.status);
				__onError();
			}
			else if(__bridge.readyState == 4) {
				__progress = 100;
				__self.loaded = true;
				__onLoad();
			}
			else {
				var p = Math.floor((getBytesLoaded() / getBytesTotal()) * 100);
				p = isNaN(p) ? 0 : p;
				__progress = p > 99 ? 99 : p;
				setTimeout(__checkProgress, 5);
			}
		}
		__checkProgress();
	};
	
	/** PUBLIC VARIABLES */
	this.loaded = false; // internal loaded boolean value
	this.lastError = ''; // last error, if there was one
	// default contentType for POST interaction
	this.contentType = 'application/x-www-form-urlencoded';
	
	/** DECLARATION OF ALL PUBLIC METHODS */
	this.load = load;
	this.send = send;
	this.sendAndLoad = sendAndLoad;
	this.decode = decode;
	this.toString = toString;
	this.addRequestHeader = addRequestHeader;
	this.getBytesLoaded = getBytesLoaded;
	this.getBytesTotal = getBytesTotal;
	this.getProgress = getProgress;
	
	/** PRIVATE VARIABLES */
	var __progress = 0;
	var __self = this;
	var __delegate = null;
	var __headers = Array();
	var __bridge = __getBridge();
};