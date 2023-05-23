/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */
"use strict";

window.addEventListener('DOMContentLoaded', function() {
	var autoCloseElement = document.getElementById("admintools-databasetools-autoclose");

	if (autoCloseElement)
	{
		window.setTimeout(function() {
			parent.admintools.Controlpanel.closeModal();
		}, 3000);

		return;
	}

	document.forms.adminForm.submit();
});