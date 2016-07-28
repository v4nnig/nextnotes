<?php
script('nextnotes', 'handlebars.min');
script('nextnotes', 'simplemde');
script('nextnotes', 'script');
style('nextnotes', 'font-awesome.min');
style('nextnotes', 'simplemde.min');
style('nextnotes', 'markdown');
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
