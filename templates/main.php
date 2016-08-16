<?php
/**
 * nextCloud - nextnotes
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Janis Koehr <janiskoehr@icloud.com>
 * @copyright Janis Koehr 2016
 */
//Select2.js for the tag field
//TODO: call of static method not allowed in app:check
\OC_Util::addVendorScript('select2/select2');
\OC_Util::addVendorStyle('select2/select2');
//simplemde.js for the WYSIWYG .md Editor
\OCP\Util::addScript('nextnotes', 'simplemde.min');
//clearsearch.js - self made: clearIcon 'X' in the searchfield
\OCP\Util::addScript('nextnotes', 'clearsearch.min');
//main app with all necessary classes and ajax call functions
\OCP\Util::addScript('nextnotes', 'script');
//Font-Awesome for pretty icons in SimpleMDE
\OCP\Util::addStyle('nextnotes', 'font-awesome.min');
//SimpleMDE styling
\OCP\Util::addStyle('nextnotes', 'simplemde.min');
//Markdown.css for beautiful preview of Notes (usability and apparel)
\OCP\Util::addStyle('nextnotes', 'markdown.min');
//main styling and css fixes for the app.
\OCP\Util::addStyle('nextnotes', 'style');
?>
<div id="app">
	<!-- nextCloud - nextnotes - This file is licensed under the Affero General Public License version 3 or later. See the COPYING file. - @author Janis Koehr <janiskoehr@icloud.com> - @copyright Janis Koehr 2016 -->
	<div id="app-navigation">
		<?php print_unescaped($this->inc('part.search')); ?>
		<?php print_unescaped($this->inc('part.navigation')); ?>
		<?php print_unescaped($this->inc('part.settings')); ?>
	</div>

	<div id="app-content">
		<div id="app-content-wrapper">
			<?php print_unescaped($this->inc('part.content')); ?>
		</div>
	</div>
</div>
