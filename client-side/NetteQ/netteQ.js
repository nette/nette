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
		if (document.addEventListener) { // non-IE
			if (event === 'mouseenter' || event === 'mouseleave') { // simulate mouseenter & mouseleave using mouseover & mouseout
				this.addEventListener(event === 'mouseenter' ? 'mouseover' : 'mouseout', function(e) {
					var target = e.relatedTarget;
					while (target && target !== this) target = target.parentNode; // target must not be this child
					if (target !== this) {
						handler.call(this, e);
					}
				}, false);

			} else {
				this.addEventListener(event, handler, false);
			}
		} else if (document.attachEvent) { // IE
			var el = this;
			this.attachEvent('on' + event, function(e) { // dont worry, 'e' is passed
				e.preventDefault = function() { this.returnValue = false }; // emulate preventDefault()
				e.stopPropagation = function() { this.cancelBubble = true }; // emulate stopPropagation()
				handler.call(el, e); // fix 'this'
			});
		}
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
	draggable: function(handle) {
		var el = this, dE = document.documentElement;

		(new NetteJs(handle || this)).bind('mousedown', function(e) {
			e.preventDefault();
			e.stopPropagation();

			if (el.nette && el.nette.isMoving) { // missed mouseup out of window?
				return dE.onmouseup();
			}

			el.nette = el.nette || {};
			el.nette.isMoving = true;
			var deltaX = el.offsetLeft - e.clientX, deltaY = el.offsetTop - e.clientY;

			dE.onmousemove = function(e) {
				e = e || event;
				NetteJs.fn.move.call(el, e.clientX + deltaX, e.clientY + deltaY);
				return false;
			}

			dE.onmouseup = function() {
				el.nette.isMoving = dE.onmousemove = dE.onmouseup = null;
				return false;
			}
		});
	}
});
