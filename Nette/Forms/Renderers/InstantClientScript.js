<script type="text/javascript">
/* <![CDATA[ */

var nette = nette || { };

nette.getValue = function(elem) {
	if (!elem) {
		var undefined;
		return undefined;
	}

	if (!elem.nodeName) { // radio
		for (var i = 0, len = elem.length; i < len; i++) {
			if (elem[i].checked) {
				return elem[i].value;
			}
		}
		return null;
	}

	if (elem.nodeName.toLowerCase() === 'select') {
		var index = elem.selectedIndex, options = elem.options;

		if (index < 0) {
			return null;

		} else if (elem.type === 'select-one') {
			return options[index].value;
		}

		for (var i = 0, values = [], len = options.length; i < len; i++) {
			if (options[i].selected) {
				values.push(options[i].value);
			}
		}
		return values;
	}

	if (elem.type === 'checkbox') {
		return elem.checked;
	}

	return elem.value.replace(/^\s+|\s+$/g, '');
}


nette.validateForm = function(sender) {
	var form = sender.form || sender;
	var formName = form.getAttributeNode('name').nodeValue;
	if (!this.forms[formName]) {
		return true;
	}

	var error, validators = this.forms[formName].validators;
	for (var name in validators) {
		error = validators[name](sender);
		if (typeof error === 'string') {
			form[name].focus();
			alert(error);
			return false;
		}
	}

	return true;
}


nette.toggle = function(id, visible) {
	var elem = document.getElementById(id);
	if (elem) {
		elem.style.display = visible ? "" : "none";
	}
}


nette.forms = nette.forms || { };

nette.forms[<?php echo $formName ?>] = {
	validators: {
<?php foreach ($this->validateScripts as $name => $validateScript): ?>
		<?php echo json_encode((string) $name) ?>: function(sender) {
			var res, form = sender.form || sender;
<?php echo /*Nette\*/String::indent($validateScript, 3) ?>

		},
<?php endforeach ?>

		null: null
	},

	toggle: function(sender) {
		var visible, res, form = document.forms[<?php echo $formName ?>];
<?php echo /*Nette\*/String::indent($this->toggleScript, 2) ?>

	}
}


<?php if ($this->toggleScript): ?>
nette.forms[<?php echo $formName ?>].toggle();
<?php endif ?>

/* ]]> */
</script>
