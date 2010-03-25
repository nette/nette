/**
 * NetteJs
 *
 * @copyright  Copyright (c) 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 */

var NetteJs = function(selector) {
	if (typeof selector === "string") {
		this[0] = document;
		return this.find(selector);

	} else if (!selector || selector.length === void 0) {
		selector = [selector];
	}

	for (var i = 0, len = selector.length; i < len; i++) {
		if (selector[i]) this[this.length++] = selector[i];
	}
};


NetteJs.prototype = {
	constructor: NetteJs,

	length: 0,

	// supported cross-browser selectors: #id  |  div  |  div.class  |  .class
	find: function(selector) {
		if (!this[0] || !selector) {
			return new NetteJs();

		} else if (document.querySelectorAll) {
			return new NetteJs(this[0].querySelectorAll(selector));

		} else if (selector.charAt(0) === '#') { // #id
			return new NetteJs(document.getElementById(selector.substring(1)));

		} else { // div  |  div.class  |  .class
			selector = selector.split('.');
			var list = this[0].getElementsByTagName(selector[0] || '*');

			if (selector[1]) {
				var $ = new NetteJs(), pattern = new RegExp('(^|\\s)' + selector[1] + '(\\s|$)');
				for (var i = 0, len = list.length; i < len; i++) {
					if (pattern.test(list[i].className)) $[$.length++] = list[i];
				}
				return $;
			} else {
				return new NetteJs(list);
			}
		}
	},

	dom: function() {
		return this[0];
	},

	each: function(callback, args) {
		for (var i = 0, res; i < this.length; i++) {
			if ((res = callback.apply(this[i], args || [])) !== void 0) { return res; }
		}
		return this;
	}
};


NetteJs.fn = {};


NetteJs.implement = function(methods) {
	for (var name in methods) {
		NetteJs.fn[name] = methods[name];
		NetteJs.prototype[name] = (function(name){
			return function() { return this.each(NetteJs.fn[name], arguments) }
		}(name));
	}
};


NetteJs.implement({
	// cross-browser event attach
	bind: function(event, handler) {
		if (document.addEventListener && (event === 'mouseenter' || event === 'mouseleave')) { // simulate mouseenter & mouseleave using mouseover & mouseout
			var old = handler;
			event = event === 'mouseenter' ? 'mouseover' : 'mouseout';
			handler = function(e) {
				for (var target = e.relatedTarget; target; target = target.parentNode) {
					if (target === this) return; // target must not be inside this
				}
				old.call(this, e);
			};
		}

		var data = this.nette = this.nette || {},
			events = data.events = data.events || {}; // use own handler queue

		if (!events[event]) {
			var el = this, // fixes 'this' in iE
				handlers = events[event] = new Array(),
				generic = NetteJs.fn.bind.genericHandler = function(e) { // dont worry, 'e' is passed in IE
					if (!e.preventDefault) e.preventDefault = function() { e.returnValue = false }; // emulate preventDefault()
					if (!e.stopPropagation) e.stopPropagation = function() { e.cancelBubble = true }; // emulate stopPropagation()
					e.stopImmediatePropagation = function() { i = handlers.length };
					for (var i = 0; i < handlers.length; i++) {
						handlers[i].call(el, e);
					}
				};

			if (document.addEventListener) { // non-IE
				this.addEventListener(event, generic, false);
			} else if (document.attachEvent) { // IE < 9
				this.attachEvent('on' + event, generic);
			}
		}

		events[event].push(handler);
	},

	// adds class to element
	addClass: function(className) {
		this.className = this.className.replace(new RegExp('(^|\\s)' + className + '(\\s|$)', 'g'), ' ') + ' ' + className;
	},

	// removes class from element
	removeClass: function(className) {
		this.className = this.className.replace(new RegExp('(^|\\s)' + className + '(\\s|$)', 'g'), ' ');
	},

	// tests whether element has given class
	hasClass: function(className) {
		return this.className.match(new RegExp('(^|\\s)' + className + '(\\s|$)'), ' ');
	},

	show: function() {
		this.style.display = 'block';
	},

	hide: function() {
		this.style.display = 'none';
	},

	// returns (total) offset for element
	offset: function(total) {
		var el = this, res = {left: el.offsetLeft, top: el.offsetTop, width: el.offsetWidth, height: el.offsetHeight};
		while (total && (el = el.offsetParent)) {
			res.left += el.offsetLeft; res.top += el.offsetTop;;
		}
		return res;
	},

	// move to new position
	move: function(left, top) {
		var pos = {left: left, top: top};
		this.nette && this.nette.onmove && this.nette.onmove.call(this, pos);
		this.style.left = (pos.left || 0) + 'px';
		this.style.top = (pos.top || 0) + 'px';
	},

	// makes element draggable
	draggable: function(options) {
		var $el = new NetteJs(this), dE = document.documentElement, dragging, options = options || {};

		(new NetteJs(options.handle || this)).bind('mousedown', function(e) {
			e.preventDefault();
			e.stopPropagation();

			if (dragging) { // missed mouseup out of window?
				return dE.onmouseup();
			}

			options.draggedClass && $el.addClass(options.draggedClass);
			options.start && options.start(e, $el);
			dragging = true;
			var deltaX = $el[0].offsetLeft - e.clientX, deltaY = $el[0].offsetTop - e.clientY;

			dE.onmousemove = function(e) {
				e = e || event;
				NetteJs.fn.move.call($el[0], e.clientX + deltaX, e.clientY + deltaY);
				return false;
			};

			dE.onmouseup = function(e) {
				options.draggedClass && $el.removeClass(options.draggedClass);
				options.stop && options.stop(e || event, $el);
				dragging = dE.onmousemove = dE.onmouseup = null;
				return false;
			};
		});
	}
});
