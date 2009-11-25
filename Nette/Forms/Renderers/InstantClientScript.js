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


nette.forms = nette.forms || { };

<?php echo $script ?>

/* ]]> */
</script>
