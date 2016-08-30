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
vendor_script('select2/select2');
vendor_style('select2/select2');
//App scripts and styles
$scripts = array('simplemde.min', 'clearsearch.min', 'nextnotesapp', 'nextnotesnotes', 'nextnotestags', 'nextnotesview');
script('nextnotes', $scripts);
$styles = array('font-awesome.min', 'simplemde.min', 'markdown.min', 'style');
style('nextnotes', $styles);
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
