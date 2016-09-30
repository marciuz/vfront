window.dhtmlHistory = { initialize: function() { if (this.isInternetExplorer() == false) { return;}
if (historyStorage.hasKey("DhtmlHistory_pageLoaded") == false) { this.fireOnNewListener = false; this.firstLoad = true; historyStorage.put("DhtmlHistory_pageLoaded", true);}
else { this.fireOnNewListener = true; this.firstLoad = false;}
}, addListener: function(callback) { this.listener = callback; if (this.fireOnNewListener == true) { this.fireHistoryEvent(this.currentLocation); this.fireOnNewListener = false;}
}, add: function(newLocation, historyData) { var self = this; var addImpl = function() { if (self.currentWaitTime > 0)
self.currentWaitTime = self.currentWaitTime - self.WAIT_TIME; newLocation = self.removeHash(newLocation); var idCheck = document.getElementById(newLocation); if (idCheck != undefined || idCheck != null) { var message = "Exception: History locations can not have " + "the same value as _any_ id's " + "that might be in the document, " + "due to a bug in Internet " + "Explorer; please ask the " + "developer to choose a history " + "location that does not match " + "any HTML id's in this " + "document. The following ID " + "is already taken and can not " + "be a location: " + newLocation; throw message;}
historyStorage.put(newLocation, historyData); self.ignoreLocationChange = true; this.ieAtomicLocationChange = true; self.currentLocation = newLocation; window.location.hash = newLocation; if (self.isInternetExplorer())
self.iframe.src = "blank.html?" + newLocation; this.ieAtomicLocationChange = false;}; window.setTimeout(addImpl, this.currentWaitTime); this.currentWaitTime = this.currentWaitTime + this.WAIT_TIME;}, isFirstLoad: function() { if (this.firstLoad == true) { return true;}
else { return false;}
}, isInternational: function() { return false;}, getVersion: function() { return "0.03";}, getCurrentLocation: function() { var currentLocation = this.removeHash(window.location.hash); return currentLocation;}, currentLocation: null, listener: null, iframe: null, ignoreLocationChange: null, WAIT_TIME: 200, currentWaitTime: 0, fireOnNewListener: null, firstLoad: null, ieAtomicLocationChange: null, create: function() { var initialHash = this.getCurrentLocation(); this.currentLocation = initialHash; if (this.isInternetExplorer()) { document.write("<iframe style='border: 0px; width: 1px; " + "height: 1px; position: absolute; bottom: 0px; " + "right: 0px; visibility: visible;' " + "name='DhtmlHistoryFrame' id='DhtmlHistoryFrame' " + "src='blank.html?" + initialHash + "'>" + "</iframe>"); this.WAIT_TIME = 400;}
var self = this; window.onunload = function() { self.firstLoad = null;}; if (this.isInternetExplorer() == false) { if (historyStorage.hasKey("DhtmlHistory_pageLoaded") == false) { this.ignoreLocationChange = true; this.firstLoad = true; historyStorage.put("DhtmlHistory_pageLoaded", true);}
else { this.ignoreLocationChange = false; this.fireOnNewListener = true;}
}
else { this.ignoreLocationChange = true;}
if (this.isInternetExplorer()) { this.iframe = document.getElementById("DhtmlHistoryFrame");}
var self = this; var locationHandler = function() { self.checkLocation();}; setInterval(locationHandler, 100);}, fireHistoryEvent: function(newHash) { var historyData = historyStorage.get(newHash); this.listener.call(null, newHash, historyData);}, checkLocation: function() { if (this.isInternetExplorer() == false
&& this.ignoreLocationChange == true) { this.ignoreLocationChange = false; return;}
if (this.isInternetExplorer() == false
&& this.ieAtomicLocationChange == true) { return;}
var hash = this.getCurrentLocation(); if (hash == this.currentLocation)
return; this.ieAtomicLocationChange = true; if (this.isInternetExplorer()
&& this.getIFrameHash() != hash) { this.iframe.src = "blank.html?" + hash;}
else if (this.isInternetExplorer()) { return;}
this.currentLocation = hash; this.ieAtomicLocationChange = false; this.fireHistoryEvent(hash);}, getIFrameHash: function() { var historyFrame = document.getElementById("DhtmlHistoryFrame"); var doc = historyFrame.contentWindow.document; var hash = new String(doc.location.search); if (hash.length == 1 && hash.charAt(0) == "?")
hash = ""; else if (hash.length >= 2 && hash.charAt(0) == "?")
hash = hash.substring(1); return hash;}, removeHash: function(hashValue) { if (hashValue == null || hashValue == undefined)
return null; else if (hashValue == "")
return ""; else if (hashValue.length == 1 && hashValue.charAt(0) == "#")
return ""; else if (hashValue.length > 1 && hashValue.charAt(0) == "#")
return hashValue.substring(1); else
return hashValue;}, iframeLoaded: function(newLocation) { if (this.ignoreLocationChange == true) { this.ignoreLocationChange = false; return;}
var hash = new String(newLocation.search); if (hash.length == 1 && hash.charAt(0) == "?")
hash = ""; else if (hash.length >= 2 && hash.charAt(0) == "?")
hash = hash.substring(1); if (this.pageLoadEvent != true) { window.location.hash = hash;}
this.fireHistoryEvent(hash);}, isInternetExplorer: function() { var userAgent = navigator.userAgent.toLowerCase(); if (document.all && userAgent.indexOf('msie')!=-1) { return true;}
else { return false;}
}
}; window.historyStorage = { debugging: false, storageHash: new Object(), hashLoaded: false, put: function(key, value) { this.assertValidKey(key); if (this.hasKey(key)) { this.remove(key);}
this.storageHash[key] = value; this.saveHashTable();}, get: function(key) { this.assertValidKey(key); this.loadHashTable(); var value = this.storageHash[key]; if (value == undefined)
return null; else
return value;}, remove: function(key) { this.assertValidKey(key); this.loadHashTable(); delete this.storageHash[key]; this.saveHashTable();}, reset: function() { this.storageField.value = ""; this.storageHash = new Object();}, hasKey: function(key) { this.assertValidKey(key); this.loadHashTable(); if (typeof this.storageHash[key] == "undefined")
return false; else
return true;}, isValidKey: function(key) { if (typeof key != "string")
key = key.toString(); var matcher = /^[a-zA-Z0-9_ \!\@\#\$\%\^\&\*\(\)\+\=\:\;\,\.\/\?\|\\\~\{\}\[\]]*$/; return matcher.test(key);}, storageField: null, init: function() { var styleValue = "position: absolute; top: -1000px; left: -1000px;"; if (this.debugging == true) { styleValue = "width: 30em; height: 30em;";}
var newContent = "<form id='historyStorageForm' " + "method='GET' " + "style='" + styleValue + "'>" + "<textarea id='historyStorageField' " + "style='" + styleValue + "'" + "left: -1000px;' " + "name='historyStorageField'></textarea>" + "</form>"; document.write(newContent); this.storageField = document.getElementById("historyStorageField");}, assertValidKey: function(key) { if (this.isValidKey(key) == false) { throw "Please provide a valid key for " + "window.historyStorage, key= " + key;}
}, loadHashTable: function() { if (this.hashLoaded == false) { var serializedHashTable = this.storageField.value; if (serializedHashTable != "" &&
serializedHashTable != null) { this.storageHash = eval('(' + serializedHashTable + ')');}
this.hashLoaded = true;}
}, saveHashTable: function() { this.loadHashTable(); var serializedHashTable = JSON.stringify(this.storageHash); this.storageField.value = serializedHashTable;}
}; Array.prototype.______array = '______array'; var JSON = { org: 'http://www.JSON.org', copyright: '(c)2005 JSON.org', license: 'http://www.crockford.com/JSON/license.html', stringify: function (arg) { var c, i, l, s = '', v; switch (typeof arg) { case 'object':
if (arg) { if (arg.______array == '______array') { for (i = 0; i < arg.length; ++i) { v = this.stringify(arg[i]); if (s) { s += ',';}
s += v;}
return '[' + s + ']';} else if (typeof arg.toString != 'undefined') { for (i in arg) { v = arg[i]; if (typeof v != 'undefined' && typeof v != 'function') { v = this.stringify(v); if (s) { s += ',';}
s += this.stringify(i) + ':' + v;}
}
return '{' + s + '}';}
}
return 'null'; case 'number':
return isFinite(arg) ? String(arg) : 'null'; case 'string':
l = arg.length; s = '"'; for (i = 0; i < l; i += 1) { c = arg.charAt(i); if (c >= ' ') { if (c == '\\' || c == '"') { s += '\\';}
s += c;} else { switch (c) { case '\b':
s += '\\b'; break; case '\f':
s += '\\f'; break; case '\n':
s += '\\n'; break; case '\r':
s += '\\r'; break; case '\t':
s += '\\t'; break; default:
c = c.charCodeAt(); s += '\\u00' + Math.floor(c / 16).toString(16) + (c % 16).toString(16);}
}
}
return s + '"'; case 'boolean':
return String(arg); default:
return 'null';}
}, parse: function (text) { var at = 0; var ch = ' '; function error(m) { throw { name: 'JSONError', message: m, at: at - 1, text: text
};}
function next() { ch = text.charAt(at); at += 1; return ch;}
function white() { while (ch != '' && ch <= ' ') { next();}
}
function str() { var i, s = '', t, u; if (ch == '"') { outer: while (next()) { if (ch == '"') { next(); return s;} else if (ch == '\\') { switch (next()) { case 'b':
s += '\b'; break; case 'f':
s += '\f'; break; case 'n':
s += '\n'; break; case 'r':
s += '\r'; break; case 't':
s += '\t'; break; case 'u':
u = 0; for (i = 0; i < 4; i += 1) { t = parseInt(next(), 16); if (!isFinite(t)) { break outer;}
u = u * 16 + t;}
s += String.fromCharCode(u); break; default:
s += ch;}
} else { s += ch;}
}
}
error("Bad string");}
function arr() { var a = []; if (ch == '[') { next(); white(); if (ch == ']') { next(); return a;}
while (ch) { a.push(val()); white(); if (ch == ']') { next(); return a;} else if (ch != ',') { break;}
next(); white();}
}
error("Bad array");}
function obj() { var k, o = {}; if (ch == '{') { next(); white(); if (ch == '}') { next(); return o;}
while (ch) { k = str(); white(); if (ch != ':') { break;}
next(); o[k] = val(); white(); if (ch == '}') { next(); return o;} else if (ch != ',') { break;}
next(); white();}
}
error("Bad object");}
function num() { var n = '', v; if (ch == '-') { n = '-'; next();}
while (ch >= '0' && ch <= '9') { n += ch; next();}
if (ch == '.') { n += '.'; while (next() && ch >= '0' && ch <= '9') { n += ch;}
}
if (ch == 'e' || ch == 'E') { n += 'e'; next(); if (ch == '-' || ch == '+') { n += ch; next();}
while (ch >= '0' && ch <= '9') { n += ch; next();}
}
v = +n; if (!isFinite(v)) { error("Bad number");} else { return v;}
}
function word() { switch (ch) { case 't':
if (next() == 'r' && next() == 'u' && next() == 'e') { next(); return true;}
break; case 'f':
if (next() == 'a' && next() == 'l' && next() == 's' &&
next() == 'e') { next(); return false;}
break; case 'n':
if (next() == 'u' && next() == 'l' && next() == 'l') { next(); return null;}
break;}
error("Syntax error");}
function val() { white(); switch (ch) { case '{':
return obj(); case '[':
return arr(); case '"':
return str(); case '-':
return num(); default:
return ch >= '0' && ch <= '9' ? num() : word();}
}
return val();}
}; window.historyStorage.init(); window.dhtmlHistory.create(); 