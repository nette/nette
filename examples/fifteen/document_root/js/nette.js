/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @version    $Revision$ $Date$
 */

var nette = {

	// public
	errorText: "Chyba pri nacitani stanky",

	spinnerId: "spinner",

	action: function(action, sender)
	{
		// create new AJAX request
		this.initAjax();
		if (!this.ajax) return false;

		action += (action.indexOf('?') == -1) ? '?' : '&';
		action += this.state + '&';

		// create process indicator
		try {
			var img = document.getElementById(this.spinnerId);
			if (sender && img) {
				this.spinner = img.cloneNode(true);
				this.spinner.style.display = 'inline';
				sender.parentNode.insertBefore(this.spinner, sender.nextSibling);
			}
		} catch (e) {
		}

		try {
			var url = action + '-r=' + Math.random();
			var query = null;

			this.ajax.open("POST", url, true);
			this.ajax.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			//this.ajax.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			this.ajax.onreadystatechange = function() { nette.ajaxHandler(); }
			this.ajax.send(query);
			return true;

		} catch (e) {
			return false;
		}
	},



	// private
	state: "",

	spinner: null,

	ajax: null,

	redirect: function(url)
	{
		//alert(url);
		document.location = url;
	},


	error: function(message)
	{
		alert(message);
	},


	updateHtml: function(id, html)
	{
		var el = document.getElementById(id);
		if (el) el.innerHTML = html;
	},


	updateState: function(state)
	{
		this.state = state;
	},


	initAjax: function()
	{
		this.ajax = false;

		if (typeof XMLHttpRequest != 'undefined') {
			this.ajax = new XMLHttpRequest();
		}

		if (!this.ajax && window.ActiveXObject) {
			try {
				this.ajax = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try {
					this.ajax = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e) {
					this.ajax = false;
				}
			}
		}
	},


	ajaxHandler: function()
	{
		if (this.ajax.readyState == 4) {
			// remove process indicator
			if (this.spinner) {
				this.spinner.parentNode.removeChild(this.spinner);
				this.spinner = null;
			}

			if (this.ajax.status == 200) {
				eval(this.ajax.responseText);
			} else  {
				this.error(this.errorText + " (" + this.ajax.status + ": " + this.ajax.statusText + ")");
			}
		}
	}

}
