/**
 * NetteForms - simple form validation.
 *
 * This file is part of the Nette Framework.
 * Copyright (c) 2004, 2013 David Grudl (http://davidgrudl.com)
 */

var Nette = Nette || {};

/**
 * Attaches a handler to an event for the element.
 */
Nette.addEvent = function(element, on, callback) {
	var original = element['on' + on];
	element['on' + on] = function() {
		if (typeof original === 'function' && original.apply(element, arguments) === false) {
			return false;
		}
		return callback.apply(element, arguments);
	};
};


/**
 * Returns the value of form element.
 */
Nette.getValue = function(elem) {
	var i, len;
	if (!elem) {
		return null;

	} else if (!elem.nodeName) { // RadioNodeList
		for (i = 0, len = elem.length; i < len; i++) {
			if (elem[i].checked) {
				return elem[i].value;
			}
		}
		return null;

	} else if (elem.nodeName.toLowerCase() === 'select') {
		var index = elem.selectedIndex, options = elem.options, values = [];

		if (elem.type === 'select-one') {
			return index < 0 ? null : options[index].value;
		}

		for (i = 0, len = options.length; i < len; i++) {
			if (options[i].selected) {
				values.push(options[i].value);
			}
		}
		return values;

	} else if (elem.type === 'checkbox') {
		return elem.checked;

	} else if (elem.type === 'radio') {
		return Nette.getValue(elem.form.elements[elem.name].nodeName ? [elem] : elem.form.elements[elem.name]);

	} else if (elem.type === 'file') {
		return elem.files || elem.value;

	} else {
		return elem.value.replace("\r", '').replace(/^\s+|\s+$/g, '');
	}
};


/**
 * Returns the effective value of form element.
 */
Nette.getEffectiveValue = function(elem) {
	var val = Nette.getValue(elem);
	if (elem.getAttribute) {
		if (val === elem.getAttribute('data-nette-empty-value')) {
			val = '';
		}
	}
	return val;
};


/**
 * Validates form element against given rules.
 */
Nette.validateControl = function(elem, rules, onlyCheck) {
	if (!elem.nodeName) { // RadioNodeList
		elem = elem[0];
	}
	rules = rules || Nette.parseJSON(elem.getAttribute('data-nette-rules'));

	for (var id = 0, len = rules.length; id < len; id++) {
		var rule = rules[id], op = rule.op.match(/(~)?([^?]+)/);
		rule.neg = op[1];
		rule.op = op[2];
		rule.condition = !!rule.rules;
		var el = rule.control ? elem.form.elements[rule.control] : elem;
		if (!el.nodeName) { // RadioNodeList
			el = el[0];
		}

		var success = Nette.validateRule(el, rule.op, rule.arg);
		if (success === null) {
			continue;
		}
		if (rule.neg) {
			success = !success;
		}

		if (rule.condition && success) {
			if (!Nette.validateControl(elem, rule.rules, onlyCheck)) {
				return false;
			}
		} else if (!rule.condition && !success) {
			if (Nette.isDisabled(el)) {
				continue;
			}
			if (!onlyCheck) {
				var arr = Nette.isArray(rule.arg) ? rule.arg : [rule.arg];
				var message = rule.msg.replace(/%(value|\d+)/g, function(foo, m) {
					return Nette.getValue(m === 'value' ? el : elem.form.elements[arr[m].control]);
				});
				Nette.addError(el, message);
			}
			return false;
		}
	}
	return true;
};


/**
 * Validates whole form.
 */
Nette.validateForm = function(sender) {
	var form = sender.form || sender, scope = false;
	if (form['nette-submittedBy'] && form['nette-submittedBy'].getAttribute('formnovalidate') !== null) {
		var scopeArr = Nette.parseJSON(form['nette-submittedBy'].getAttribute('data-nette-validation-scope'));
		if (scopeArr.length) {
			scope = new RegExp('^(' + scopeArr.join('-|') + '-)');
		} else {
			return true;
		}
	}

	var radios = {}, i, elem;

	for (i = 0; i < form.elements.length; i++) {
		elem = form.elements[i];

		if (elem.type === 'radio') {
			if (radios[elem.name]) {
				continue;
			}
			radios[elem.name] = true;
		}

		if ((scope && !elem.name.replace(/]\[|\[|]|$/g, '-').match(scope)) || Nette.isDisabled(elem)) {
			continue;
		}

		if (!Nette.validateControl(elem)) {
			return false;
		}
	}
	return true;
};


/**
 * Check if input is disabled.
 */
Nette.isDisabled = function(elem) {
	if (elem.type === 'radio') {
		elem = elem.form.elements[elem.name].nodeName ? [elem] : elem.form.elements[elem.name];
		for (var i = 0; i < elem.length; i++) {
			if (!elem[i].disabled) {
				return false;
			}
		}
		return true;
	}
	return elem.disabled;
};


/**
 * Display error message.
 */
Nette.addError = function(elem, message) {
	if (elem.focus) {
		elem.focus();
	}
	if (message) {
		alert(message);
	}
};


/**
 * Expand rule argument.
 */
Nette.expandRuleArgument = function(elem, arg) {
	if (arg && arg.control) {
		arg = Nette.getEffectiveValue(elem.form.elements[arg.control]);
	}
	return arg;
};


/**
 * Validates single rule.
 */
Nette.validateRule = function(elem, op, arg) {
	var val = Nette.getEffectiveValue(elem);

	if (op.charAt(0) === ':') {
		op = op.substr(1);
	}
	op = op.replace('::', '_');
	op = op.replace(/\\/g, '');

	var arr = Nette.isArray(arg) ? arg.slice(0) : [arg];
	for (var i = 0, len = arr.length; i < len; i++) {
		arr[i] = Nette.expandRuleArgument(elem, arr[i]);
	}
	return Nette.validators[op] ? Nette.validators[op](elem, Nette.isArray(arg) ? arr : arr[0], val) : null;
};


Nette.validators = {
	filled: function(elem, arg, val) {
		return val !== '' && val !== false && val !== null;
	},

	blank: function(elem, arg, val) {
		return !Nette.validators.filled(elem, arg, val);
	},

	valid: function(elem, arg, val) {
		return Nette.validateControl(elem, null, true);
	},

	equal: function(elem, arg, val) {
		if (arg === undefined) {
			return null;
		}
		val = Nette.isArray(val) ? val : [val];
		arg = Nette.isArray(arg) ? arg : [arg];
		loop:
		for (var i1 = 0, len1 = val.length; i1 < len1; i1++) {
			for (var i2 = 0, len2 = arg.length; i2 < len2; i2++) {
				if (val[i1] == arg[i2]) {
					continue loop;
				}
			}
			return false;
		}
		return true;
	},

	notEqual: function(elem, arg, val) {
		return arg === undefined ? null : !Nette.validators.equal(elem, arg, val);
	},

	minLength: function(elem, arg, val) {
		return val.length >= arg;
	},

	maxLength: function(elem, arg, val) {
		return val.length <= arg;
	},

	length: function(elem, arg, val) {
		arg = Nette.isArray(arg) ? arg : [arg, arg];
		return (arg[0] === null || val.length >= arg[0]) && (arg[1] === null || val.length <= arg[1]);
	},

	email: function(elem, arg, val) {
		return (/^("([ !\x23-\x5B\x5D-\x7E]*|\\[ -~])+"|[-a-z0-9!#$%&'*+\/=?^_`{|}~]+(\.[-a-z0-9!#$%&'*+\/=?^_`{|}~]+)*)@([0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)+[a-z\u00C0-\u02FF\u0370-\u1EFF][-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF]$/i).test(val);
	},

	url: function(elem, arg, val) {
		return (/^(https?:\/\/|(?=.*\.))([0-9a-z\u00C0-\u02FF\u0370-\u1EFF](([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)*[a-z\u00C0-\u02FF\u0370-\u1EFF][-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF]|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|\[[0-9a-f:]{3,39}\])(:\d{1,5})?(\/\S*)?$/i).test(val);
	},

	regexp: function(elem, arg, val) {
		var parts = typeof arg === 'string' ? arg.match(/^\/(.*)\/([imu]*)$/) : false;
		if (parts) { try {
			return (new RegExp(parts[1], parts[2].replace('u', ''))).test(val);
		} catch (e) {} }
	},

	pattern: function(elem, arg, val) {
		try {
			return typeof arg === 'string' ? (new RegExp('^(' + arg + ')$')).test(val) : null;
		} catch (e) {}
	},

	integer: function(elem, arg, val) {
		return (/^-?[0-9]+$/).test(val);
	},

	'float': function(elem, arg, val) {
		return (/^-?[0-9]*[.,]?[0-9]+$/).test(val);
	},

	range: function(elem, arg, val) {
		return Nette.isArray(arg) ?
			((arg[0] === null || parseFloat(val) >= arg[0]) && (arg[1] === null || parseFloat(val) <= arg[1])) : null;
	},

	submitted: function(elem, arg, val) {
		return elem.form['nette-submittedBy'] === elem;
	},

	fileSize: function(elem, arg, val) {
		if (window.FileList) {
			for (var i = 0; i < val.length; i++) {
				if (val[i].size > arg) {
					return false;
				}
			}
		}
		return true;
	}
};


/**
 * Process all toggles in form.
 */
Nette.toggleForm = function(form, firsttime) {
	var i;
	Nette.toggles = {};
	for (i = 0; i < form.elements.length; i++) {
		if (form.elements[i].nodeName.toLowerCase() in {input: 1, select: 1, textarea: 1, button: 1}) {
			Nette.toggleControl(form.elements[i], null, null, firsttime);
		}
	}

	for (i in Nette.toggles) {
		Nette.toggle(i, Nette.toggles[i]);
	}
};


/**
 * Process toggles on form element.
 */
Nette.toggleControl = function(elem, rules, topSuccess, firsttime) {
	rules = rules || Nette.parseJSON(elem.getAttribute('data-nette-rules'));
	var has = false, __hasProp = Object.prototype.hasOwnProperty, handler = function() {
		Nette.toggleForm(elem.form);
	};

	for (var id = 0, len = rules.length; id < len; id++) {
		var rule = rules[id], op = rule.op.match(/(~)?([^?]+)/);
		rule.neg = op[1];
		rule.op = op[2];
		rule.condition = !!rule.rules;
		if (!rule.condition) {
			continue;
		}

		var el = rule.control ? elem.form.elements[rule.control] : elem;
		var success = topSuccess;
		if (success !== false) {
			success = Nette.validateRule(el, rule.op, rule.arg);
			if (success === null) {
				continue;
			}
			if (rule.neg) {
				success = !success;
			}
		}

		if (Nette.toggleControl(elem, rule.rules, success, firsttime) || rule.toggle) {
			has = true;
			if (firsttime) {
				var oldIE = !document.addEventListener, // IE < 9
					els = el.nodeName ? [el] : el; // is radiolist?

				for (var i = 0; i < els.length; i++) {
					Nette.addEvent(els[i], oldIE && el.type in {checkbox: 1, radio: 1} ? 'click' : 'change', handler);
				}
			}
			for (var id2 in rule.toggle || []) {
				if (__hasProp.call(rule.toggle, id2)) {
					Nette.toggles[id2] = Nette.toggles[id2] || (success && rule.toggle[id2]);
				}
			}
		}
	}
	return has;
};


Nette.parseJSON = function(s) {
	s = s || '[]';
	if (s.substr(0, 3) === '{op') {
		return eval('[' + s + ']'); // backward compatibility
	}
	return window.JSON && window.JSON.parse ? JSON.parse(s) : eval(s);
};


/**
 * Displays or hides HTML element.
 */
Nette.toggle = function(id, visible) {
	var elem = document.getElementById(id);
	if (elem) {
		elem.style.display = visible ? '' : 'none';
	}
};


/**
 * Setup handlers.
 */
Nette.initForm = function(form) {
	form.noValidate = 'novalidate';

	Nette.addEvent(form, 'submit', function(e) {
		if (!Nette.validateForm(form)) {
			if (e && e.stopPropagation) {
				e.stopPropagation();
			} else if (window.event) {
				event.cancelBubble = true;
			}
			return false;
		}
	});

	Nette.addEvent(form, 'click', function(e) {
		e = e || event;
		var target = e.target || e.srcElement;
		form['nette-submittedBy'] = (target.type in {submit: 1, image: 1}) ? target : null;
	});

	Nette.toggleForm(form, true);
};


/**
 * Determines whether the argument is an array.
 */
Nette.isArray = function(arg) {
	return Object.prototype.toString.call(arg) === '[object Array]';
};


Nette.addEvent(window, 'load', function() {
	for (var i = 0; i < document.forms.length; i++) {
		Nette.initForm(document.forms[i]);
	}
});


/**
 * Converts string to web safe characters [a-z0-9-] text.
 */
Nette.webalize = function(s) {
	s = s.toLowerCase();
	var res = '', i, ch;
	for (i = 0; i < s.length; i++) {
		ch = Nette.webalizeTable[s.charAt(i)];
		res += ch ? ch : s.charAt(i);
	}
	return res.replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
};

Nette.webalizeTable = {\u00e1: 'a', \u010d: 'c', \u010f: 'd', \u00e9: 'e', \u011b: 'e', \u00ed: 'i', \u0148: 'n', \u00f3: 'o', \u0159: 'r', \u0161: 's', \u0165: 't', \u00fa: 'u', \u016f: 'u', \u00fd: 'y', \u017e: 'z'};
