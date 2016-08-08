<script id="settings-tpl" type="text/x-handlebars-template">
	<select class="nextnotes-delete-tag">
		<option selected disabled><?php p($l->t('Choose tag')); ?></option>
		{{#each tags}}
			<option value="{{ this }}">{{ this }}</option>
		{{/each}}
	</select>
</script>

<div id="app-settings">
	<div id="app-settings-header">
		<button class="settings-button"
				data-apps-slide-toggle="#app-settings-content"
		><?php p($l->t('Delete Tags')); ?></button>
	</div>
	<div id="app-settings-content">
	</div>
</div>