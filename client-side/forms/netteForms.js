/**
 * NetteForms - simple form validation.
 *
 * This file is part of the Nette Framework.
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 */

var Nette = Nette || {};

Nette.addEvent = function (element, on, callback) {
	var original = element['on' + on];
	element['on' + on] = function () {
		if (typeof original === 'function' && original.apply(element, arguments) === false) {
			return false;
		}
		return callback.apply(element, arguments);
	};
};


Nette.getValue = function(elem) {
	var i, len;
	if (!elem) {
		return null;

	} else if (!elem.nodeName) { // radio
		for (i = 0, len = elem.length; i < len; i++) {
			if (elem[i].checked) {
				return elem[i].value;
			}
		}
		return null;

	} else if (elem.nodeName.toLowerCase() === 'select') {
		var index = elem.selectedIndex, options = elem.options;

		if (index < 0) {
			return null;

		} else if (elem.type === 'select-one') {
			return options[index].value;
		}

		for (i = 0, values = [], len = options.length; i < len; i++) {
			if (options[i].selected) {
				values.push(options[i].value);
			}
		}
		return values;

	} else if (elem.type === 'checkbox') {
		return elem.checked;

	} else if (elem.type === 'radio') {
		return Nette.getValue(elem.form.elements[elem.name]);

	} else {
		return elem.value.replace(/^\s+|\s+$/g, '');
	}
};


Nette.validateControl = function(elem, rules, onlyCheck) {
	rules = rules || eval('[' + (elem.getAttribute('data-nette-rules') || '') + ']');
	for (var id in rules) {
		var rule = rules[id], op = rule.op.match(/(~)?([^?]+)/);
		rule.neg = op[1];
		rule.op = op[2];
		rule.condition = !!rule.rules;
		var el = rule.control ? elem.form.elements[rule.control] : elem;

		var success = Nette.validateRule(el, rule.op, rule.arg);
		if (success === null) { continue; }
		if (rule.neg) { success = !success; }

		if (rule.condition && success) {
			if (!Nette.validateControl(elem, rule.rules, onlyCheck)) {
				return false;
			}
		} else if (!rule.condition && !success) {
			if (el.disabled) { continue; }
			if (!onlyCheck) {
				Nette.addError(el, rule.msg.replace('%value', Nette.getValue(el)));
			}
			return false;
		}
	}
	return true;
};


Nette.validateForm = function(sender) {
	var form = sender.form || sender;
	if (form['nette-submittedBy'] && form['nette-submittedBy'].getAttribute('formnovalidate') !== null) {
		return true;
	}
	for (var i = 0; i < form.elements.length; i++) {
		var elem = form.elements[i];
		if (!(elem.nodeName.toLowerCase() in {input:1, select:1, textarea:1}) || (elem.type in {hidden:1, submit:1, image:1, reset: 1}) || elem.disabled || elem.readonly) {
			continue;
		}
		if (!Nette.validateControl(elem)) {
			return false;
		}
	}
	return true;
};


Nette.addError = function(elem, message) {
	if (elem.focus) {
		elem.focus();
	}
	if (message) {
		alert(message);
	}
};

Nette.validators = { };

Nette.validators['filled'] = function(elem, op, arg, val) {
	return val !== '' && val !== false && val !== null;
}

Nette.validators['valid'] = function(elem, op, arg, val) {
	return Nette.validateControl(elem, null, true);
}

Nette.validators['equal'] = function(elem, op, arg, val) {
	arg = arg instanceof Array ? arg : [arg];
	for (var i in arg) {
		if (val == (arg[i].control ? Nette.getValue(elem.form.elements[arg[i].control]) : arg[i])) {
			return true;
		}
	}
	return false;
}

Nette.validators['minLength'] = function(elem, op, arg, val) {
	return val.length >= arg;
}

Nette.validators['maxLength'] = function(elem, op, arg, val) {
	return val.length <= arg;
}

Nette.validators['length'] = function(elem, op, arg, val) {
	if (typeof arg !== 'object') {
		arg = [arg, arg];
	}
	return (arg[0] === null || val.length >= arg[0]) && (arg[1] === null || val.length <= arg[1]);
}

Nette.validators['email'] = function(elem, op, arg, val) {
	return (/^[^@\s]+@[^@\s]+\.[a-z]{2,10}$/i).test(val);
}

Nette.validators['url'] = function(elem, op, arg, val) {
	return (/^.+\.[a-z]{2,6}(\/.*)?$/i).test(val);
}

Nette.validators['regexp'] = function(elem, op, arg, val) {
	var parts = arg.match(/^\/(.*)\/([imu]*)$/);
	if (parts) { try {
		return (new RegExp(parts[1], parts[2].replace('u', ''))).test(val);
	} catch (e) {} }
	return;
}

Nette.validators['pattern'] = function(elem, op, arg, val) {
	return (new RegExp(arg)).test(val);
}

Nette.validators['integer'] = function(elem, op, arg, val) {
	return (/^-?[0-9]+$/).test(val);
}

Nette.validators['float'] = function(elem, op, arg, val) {
	return (/^-?[0-9]*[.,]?[0-9]+$/).test(val);
}

Nette.validators['range'] = function(elem, op, arg, val) {
	return (arg[0] === null || parseFloat(val) >= arg[0]) && (arg[1] === null || parseFloat(val) <= arg[1]);
}

Nette.validators['submitted'] = function(elem, op, arg, val) {
	return elem.form['nette-submittedBy'] === elem;
}

Nette.validateRule = function(elem, op, arg) {
	var val = Nette.getValue(elem);

	if (elem.getAttribute) {
		if (val === elem.getAttribute('data-nette-empty-value')) val = null;
	}

	var fnc;
	if(op.charAt(0) == ':') {
		fnc = Nette.validators[op.substr(1)];
		if(fnc) {
			return fnc(elem, op, arg, val);
		}
	} else {
		var name = op.match(/::(validate)?(.*)$/)[2];
		name = name.charAt(0).toLowerCase() + name.substr(1);
		fnc = Nette.validators[name];
		if(fnc) {
			return fnc(elem, op, arg, val);
		}
	}
	return null;
};


Nette.toggleForm = function(form) {
	for (var i = 0; i < form.elements.length; i++) {
		if (form.elements[i].nodeName.toLowerCase() in {input:1, select:1, textarea:1, button:1}) {
			Nette.toggleControl(form.elements[i]);
		}
	}
};


Nette.toggleControl = function(elem, rules, firsttime) {
	rules = rules || eval('[' + (elem.getAttribute('data-nette-rules') || '') + ']');
	var has = false, handler = function() { Nette.toggleForm(elem.form); };

	for (var id in rules) {
		var rule = rules[id], op = rule.op.match(/(~)?([^?]+)/);
		rule.neg = op[1];
		rule.op = op[2];
		rule.condition = !!rule.rules;
		if (!rule.condition) { continue; }

		var el = rule.control ? elem.form.elements[rule.control] : elem;
		var success = Nette.validateRule(el, rule.op, rule.arg);
		if (success === null) { continue; }
		if (rule.neg) { success = !success; }

		if (Nette.toggleControl(elem, rule.rules, firsttime) || rule.toggle) {
			has = true;
			if (firsttime) {
				if (!el.nodeName) { // radio
					for (var i = 0; i < el.length; i++) {
						Nette.addEvent(el[i], 'click', handler);
					}
				} else if (el.nodeName.toLowerCase() === 'select') {
					Nette.addEvent(el, 'change', handler);
				} else {
					Nette.addEvent(el, 'click', handler);
				}
			}
			for (var id2 in rule.toggle || []) {
				Nette.toggle(id2, success ? rule.toggle[id2] : !rule.toggle[id2]);
			}
		}
	}
	return has;
};


Nette.toggle = function(id, visible) {
	var elem = document.getElementById(id);
	if (elem) {
		elem.style.display = visible ? "" : "none";
	}
};


Nette.initForm = function(form) {
	form.noValidate = true;

	Nette.addEvent(form, 'submit', function() {
		return Nette.validateForm(form);
	});

	Nette.addEvent(form, 'click', function(e) {
		e = e || event;
		var target = e.target || e.srcElement;
		form['nette-submittedBy'] = (target.type in {submit:1, image:1}) ? target : null;
	});

	for (var i = 0; i < form.elements.length; i++) {
		Nette.toggleControl(form.elements[i], null, true);
	}

	if (/MSIE/.exec(navigator.userAgent)) {
		var labels = {},
			wheelHandler = function() { return false; },
			clickHandler = function() { document.getElementById(this.htmlFor).focus(); return false; };

		for (i = 0, elms = form.getElementsByTagName('label'); i < elms.length; i++) {
			labels[elms[i].htmlFor] = elms[i];
		}

		for (i = 0, elms = form.getElementsByTagName('select'); i < elms.length; i++) {
			Nette.addEvent(elms[i], 'mousewheel', wheelHandler); // prevents accidental change in IE
			if (labels[elms[i].htmlId]) {
				Nette.addEvent(labels[elms[i].htmlId], 'click', clickHandler); // prevents deselect in IE 5 - 6
			}
		}
	}
};


Nette.addEvent(window, 'load', function () {
	for (var i = 0; i < document.forms.length; i++) {
		Nette.initForm(document.forms[i]);
	}
});
