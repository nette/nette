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
 * @version    $Id$
 */

var fifteen = {

	move: function(snippetId, x, y, dx, dy)
	{
		var snippet = nette.result.snippets[snippetId];
		delete nette.result.snippets[snippetId];

		nette.processing += 1;

		var el = document.getElementById(snippetId);
		var img = $('table tr:eq(' + (3-y) + ') td:eq(' + (3-x) + ') img', el);
		img.parent().replaceWith(img);
		img.css('z-index', 1000);
		img.animate({
			'left': dx * img.attr('width') + 'px',
			'top': dy * img.attr('height') + 'px'
		});
		img.queue(function () {
			nette.updateSnippet(snippetId, snippet);
			nette.processing -= 1;
		});
	}

}
