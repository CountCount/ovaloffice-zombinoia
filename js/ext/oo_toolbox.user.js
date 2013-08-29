/** OO Toolbox user script
 * version 3.8
 * February 2012
 * Copyright (c) 2011, Paul Bruhn
 * Released under the GPL license (http://www.gnu.org/copyleft/gpl.html)
 *
 * −−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−----------
 *
 * This is a Greasemonkey user script.
 *
 * To install, you need Greasemonkey: http://www.greasespot.net/
 * Then restart Firefox and revisit this script.
 * Under Tools, there will be a new menu item to "Install User Script".
 * Accept the default configuration and install.
 *
 * To uninstall, go to Tools/Manage User Scripts,
 * select "OO Toolbox", and click Uninstall.
 *
 * −−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−−----------
 *
 * @author  Paul Bruhn <ovaloffice.d2n@gmail.com>
 * @link    http://d2n.sindevel.com/oo/toolbox/
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @charset UTF-8
*/
// ==UserScript==
// @name 		OO Toolbox (D2N)
// @namespace 	http://d2n.sindevel.com/oo/toolbox/
// @description Comfort script for "Die2Nite"
// @include 	http://www.die2nite.com/*
// @version    	3.8
// ==/UserScript==

(function(){
	var v = 3.8;
	var userKey = null;

	var style = document.createElement('style');
		style.setAttribute('type', 'text/css');
		document.getElementsByTagName('head')[0].appendChild(style);
		
	var root = document.createElement('div');
		root.setAttribute('id', 'oo-toolbox-wrapper');
		root.setAttribute('title', 'Oval Office toolbox');
		root.innerHTML = '<div id="oo-toolbox"><h3 id="oot-title">Oval Office toolbox</h3><div id="oo-toolbox-xwrapper"><a id="oo-directlink" href="#" target="_new"></a></div><iframe id="oo-toolbox-basicdata" class="oo-toolbox-content"></iframe></div>';
	var ooparent = document.getElementById('contentBg');
		
	var xhr = null;
	xhr = new XMLHttpRequest();
	if (xhr) {
			xhr.open('GET', '/disclaimer?id=10;rand='  + Math.random(), true);
			xhr.onreadystatechange = function () {
					if (xhr.readyState == 4 && /name=\"key\"\s+value=\"([a-zA-Z0-9]+)\"/.test(this.responseText)) {
							ooparent.insertBefore(root,ooparent.childNodes[0]);
							var uk = RegExp.$1;
							var ooif = document.getElementById('oo-toolbox-basicdata');
							ooif.setAttribute('src', 'http://d2n.sindevel.com/oo/toolbox.php?v='+v+'&uk='+uk);
							document.getElementById('oo-directlink').setAttribute('href', 'http://d2n.sindevel.com/oo/?key='+uk+'&openmail=1');
					}
			};
			xhr.send(null);
	}
	addStyle('#oo-toolbox-wrapper { position: relative; z-index: 4; width: 100%; }');
	addStyle('#oo-toolbox { color: #000; background: transparent; position:absolute; right:175px; top:14px; width:420px; height:125px; z-index:4; margin:0; padding: 1px 0 0; }');
	addStyle('#oo-toolbox h3 { font-weight: normal; font-variant: small-caps; color: #DDAA5F; border-bottom: 1px solid #a73; border-top: 1px solid #a73; margin:0; padding: 0 5px; height: 12px; line-height: 12px; font-size: 12px; background: transparent url("http://d2n.sindevel.com/oo/img/ootbbg.png") right top no-repeat; margin-top: 2px; font-size: 12px; line-height: 11px; }');
	addStyle('.oo-toolbox-content { position: absolute; border:none; height: 100px; width: 420px; background: transparent; margin:0; padding: .5em; color: rgb(112, 64, 24); }');
	addStyle('#oo-toolbox-xwrapper { position: absolute; width: 92px; left: 310px; height: 32px; top: 45px; z-index: 9; background: rgba(255,255,255,.1); border: 1px solid #fff; border-radius: 6px; }');
	addStyle('#oo-toolbox-xwrapper a { position: absolute; width: 92px; left: 0; height: 32px; top: 0; display: block; }');
	
	function addStyle(rule) {
		try {
			return style.sheet.insertRule(rule, style.sheet.cssRules.length);
		}
		catch(e) { console.error('Failed to insert CSS rule (' + rule + ')'); }
	};
	
})();
