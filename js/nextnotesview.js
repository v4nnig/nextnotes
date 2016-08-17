/**
 * nextCloud - nextnotes
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Janis Koehr <janiskoehr@icloud.com>
 * @copyright Janis Koehr 2016
 */
(function() {
	/**
	 * Constructor of the View object.
	 * This will update the different parts of the html.
	 * @param notes
	 * @param tags
	 * @constructor
	 */
	var View = function (notes, tags, simplemde) {
		this._notes = notes;
		this._tags = tags;
		this._simplemde = simplemde;
	};

	/**
	 * Class prototype for the View. Following functions are available:
	 * @type {{renderContent: View.renderContent, renderNavigation: View.renderNavigation,
		 * renderInfoView: View.renderInfoView, adoptTheming: View.adoptTheming, searchHelper: View.searchHelper,
		 * renderTagManager: View.renderTagManager, render: View.render}}
	 */
	View.prototype = {
		/**
		 * Render the content (set the value of the SimpleMD Editor) if a note is active.
		 */
		renderContent: function () {
			var self = this;
			if(self._notes.getActive() !== undefined){
				self._simplemde.value(self._notes.getActive().content);
			}else{
				self._simplemde.value('');
			}
		},
		/**
		 * Render the navigation (adjust it if there is a search ongoing or if there are no results).
		 */
		renderNavigation: function () {
			var self = this;
			//Handlebars.js thing
			var source = $('#navigation-tpl').html();
			/** global: Handlebars */
			var template = Handlebars.compile(source);
			var view = [];
			var html = '';
			var viewnotes = self._notes.getAll();
			if(viewnotes.length > 0){
				var viewtags = self._tags.getAllRelatedTags();
				$.each(viewnotes, function(index,element){
					view.push({id: element.id, title: element.title, content: element.content, active: element.active});
					$.each(viewtags, function(i, e){
						if(element.id == i){
							view[index].tags = e;
						}
					});
				});
				html = template({notes: view, noMatches: false});
			}else{
				html = template({notes: view, noMatches: true});
			}
			$('#app-navigation ul').html(html);
			// show app menu for specific note
			$('#app-navigation .app-navigation-entry-utils-menu-button').click(function () {
				var entry = $(this).closest('.note');
				entry.find('.app-navigation-entry-menu').toggleClass('open');
			});
			// register on click action for deletion of a note
			$('#app-navigation .note .delete').click(function () {
				var entry = $(this).closest('.note');
				entry.find('.app-navigation-entry-menu').removeClass('open');

				self._notes.removeActive().done(function () {
					self.render();
				}).fail(function () {
					OC.Notification.showTemporary(t('nextnotes','Could not delete note. Note not found.'), {
						timeout: 10,
						isHTML: false
					});
				});
			});
			//Searchbox thing
			var formSearchbox = $('form.nextnotes-searchbox');
			var searchbox = $('#nextnotes-searchbox');
			//destroy all registered events for the searchbox
			formSearchbox.off('submit.nextnotes');
			searchbox.off('keyup.nextnotes');
			//register the search for notes action
			formSearchbox.on('submit.nextnotes', function(event) {
				event.stopPropagation();
				event.preventDefault();
			});
			searchbox.on('keyup.nextnotes', function(event) {
				//enter
				if (event.keyCode === 13) {
					self.searchHelper();
				}
			});
			//add the "x" for the search field
			if($('.clearInput').length){
				$('.clearicon').unwrap();
				$('.clearInput').remove();
			}
			$('.clearicon').clearIcon({"callback":function(){
				self.searchHelper();
			}});
			// register the load a note action
			$('#app-navigation .note > a').click(function () {
				var id = parseInt($(this).parent().data('id'), 10);
				$.when(self._notes.load(id)).done(self.render());
				if(simplemde.isPreviewActive()){
					simplemde.togglePreview();
				}
			});
			//Register search action for on click tag event
			$('.nextnotes-navigation-tag').on('click', function(){
				searchbox.val('#'+$(this).text()+'#');
				self.searchHelper();
			});
		},
		/**
		 * Render the InfoView at the bottom below the editor the tagfield is located here)
		 */
		renderInfoView: function(){
			var self = this;
			var inputElement = $('#nextnotesTagsInput');
			// remove the select2 thing
			inputElement.select2("destroy");
			inputElement.remove();
			// check if activeNote is available
			if(self._notes.getActive() !== undefined){
				// (re)initialize select2 thing
				var input = $("<input>", {id: "nextnotesTagsInput", "type": "hidden"});
				$('.editor-statusbar').prepend(input);
				var select2Element = $('#nextnotesTagsInput');
				var tags = [];
				$.each(self._tags.getAvailableTags(), function(key, value) {
					tags.push({id:value,text:value});
				});
				//init new select2
				select2Element.select2({
					tags: tags,
					// OPTIONS
					containerCssClass: 'nextnotes-select2-container',
					dropdownCssClass: 'nextnotes-select2-dropdown',
					placeholder: t('nextnotes', 'Add tags here...'),
					closeOnSelect: false,
					allowClear: false,
					// FORMATTING (possible messages)
					formatNoMatches: function(){
						return t('nextnotes', 'No matches.');
					},
					formatSearching: t('nextnotes', 'Searching...')
				});
				//Register the different actions for the tags in the select2 tag field.
				// INITIAL SELECTION
				select2Element.val(self._tags.getRelatedTags(self._notes.getActive().id)).trigger("change");
				// UNTAG EVENT
				select2Element.on("select2-removing", function(e){
					self._tags.unTag(self._notes.getActive().id, e.choice.id).done(function(){
						self.render();
					}).fail(function(){
						/** global: event */
						event.preventDefault();
						OC.Notification.showTemporary(t('nextnotes','Could not untag.'), {
							timeout: 10,
							isHTML: false
						});
					});
				});
				// CREATE TAG EVENT
				select2Element.on("change", function(e) {
					if (e.added !== undefined){
						var create = {id: self._notes.getActive().id, title: e.added.text};
						$.when(self._tags.createTag(create)).done(function(){
							self.render();
						}).fail(function(){
							OC.Notification.showTemporary(t('nextnotes','Could not create tag.'), {
								timeout: 10,
								isHTML: false
							});
						});
					}
				});
				//Register search action for on click tag event
				$('.select2-search-choice>div').on('click', function(){
					$('#nextnotes-searchbox').val('#'+$(this).text()+'#');
					self.searchHelper();
				});
			}
		},
		/**
		 * This is the search helper function:
		 * it triggers the search or clears the search.
		 */
		searchHelper: function(){
			var self = this;
			var searchbox = $('#nextnotes-searchbox');
			//if searchbox is empty, loadAll notes and related tags.
			if(!searchbox.val()){
				$.when(self._notes.loadAll()).done(function(){
					$.when(self._tags.loadRelatedTags()).done(function(){
						self.render();
					});
				}).fail(function(){
					OC.Notification.showTemporary(t('nextnotes','Could not load notes.'), {
						timeout: 10,
						isHTML: false
					});
				});
			}else {
				// if searchbox has input, fire the search and get results rendered.
				var search = {
					query: searchbox.val()
				};
				$.when(self._notes.search(search)).done(function () {
					$.when(self._tags.loadAll()).done(function(){
						self.render();
					});
				}).fail(function () {
					OC.Notification.showTemporary(t('nextnotes','Could not search for notes.'), {
						timeout: 10,
						isHTML: false
					});
				});
			}
		},
		/**
		 * Render the Tag Manager part (gives the opportunity to completely delte a tag in the settings part of the navigation.
		 */
		renderTagManager: function(){
			var self = this;
			//Handlebars.js thing
			var source = $('#settings-tpl').html();
			/** global: Handlebars */
			var template = Handlebars.compile(source);
			var html = template({tags: self._tags.getAvailableTags()});
			$('#app-settings-content').html(html);
			// Register the on change action for the deletion dialog for a specific tag
			$('.nextnotes-delete-tag').on('change', function(){
				var name = $(this).find(':selected').val();
				OC.dialogs.confirm(t('nextnotes', 'Are you really sure you want to delete the tag "{tag}"?',
					{tag: name}),
					t('nextnotes', 'Delete tag'), function(answer) {
						if(answer) {
							$.when(self._tags.deleteTag(name)).done(function(){
								self.render();
							});
						}
					});
			});
		},
		/**
		 * Get the current color scheme and adopt it for the tags and other stuff.
		 * The app should look as fancy as the user chooses in the settings.
		 */
		adoptTheming: function(){
			//invert color
			var color = '#fff';
			if(OCA.Theming.inverted){
				color = '#000';
			}
			$(".select2-search-choice").css({"background-color" : OCA.Theming.color, "color": color});
			$(".nextnotes-navigation-tag").css({"background-color" : OCA.Theming.color, "color": color});
		},
		/**
		 * Render all parts of the UI.
		 */
		render: function () {
			this.renderContent();
			this.renderNavigation();
			this.renderInfoView();
			this.renderTagManager();
			this.adoptTheming();
		}
	};
	OCA.NextNotes.View = View;
})();
