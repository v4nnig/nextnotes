<?php
\OC_Util::addVendorScript('select2/select2');
\OC_Util::addVendorStyle('select2/select2');
script('nextnotes', 'simplemde.min');
script('nextnotes', 'script');
style('nextnotes', 'font-awesome.min');
style('nextnotes', 'simplemde.min');
style('nextnotes', 'markdown.min');
style('nextnotes', 'style');
?>

<div id="app">
	<div id="app-navigation">
		<?php print_unescaped($this->inc('part.navigation')); ?>
		<?php print_unescaped($this->inc('part.settings')); ?>
	</div>

	<div id="app-content">
		<div id="app-content-wrapper">
			<?php print_unescaped($this->inc('part.content')); ?>
		</div>
	</div>
</div>
