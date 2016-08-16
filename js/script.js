/**
 * nextCloud - nextnotes
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Janis Koehr <janiskoehr@icloud.com>
 * @copyright Janis Koehr 2016
 */
//TODO: rework the whole code and separate the different classes from each other.. maybe separate them into different files or at least make the initialize process easier and so on.
(function (OC, window, $, undefined) {
	'use strict';
	$(document).ready(function () {
		/**
		 * Constructor of the Tags object
		 * which holds all the tags for the view.
		 * @param notes
         * @constructor
         */
		var Tags = function (notes){
			this._baseUrl = OC.generateUrl('/apps/nextnotes/tags');
			this._notes = notes;
			this._availableTags = [];
			this._relatedTags = [];
		};

		/**
		 * Class prototype for Tags. Following functions are available:
		 * @type {{createTag: Tags.createTag, loadRelatedTags: Tags.loadRelatedTags,
		 * unTag: Tags.unTag, deleteTag: Tags.deleteTag, loadAvailableTags: Tags.loadAvailableTags,
		 * getAvailableTags: Tags.getAvailableTags, getRelatedTags: Tags.getRelatedTags,
		 * getAllRelatedTags: Tags.getAllRelatedTags, loadAll: Tags.loadAll}}
         */
		Tags.prototype = {
			/**
			 * AJAX call.
			 * Creates a tag for the given create-object.
			 * The object has to fulfill the requirements
			 * of the API specification:
			 * create = {id: noteId, title: tagTitle}
			 * @param create
             * @returns $.Deferred()
             */
			createTag: function (create) {
				var deferred = $.Deferred();
				var self = this;
				$.ajax({
					url: OC.generateUrl('/apps/nextnotes/tagging'),
					method: 'POST',
					contentType: 'application/json',
					data: JSON.stringify(create)
				}).done(function () {
					$.when(self.loadAll()).done(function(){
						deferred.resolve();
					});
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
			/**
			 * AJAX call.
			 * Loads the related tags for all available notes.
			 * @returns $.Deferred()
             */
			loadRelatedTags: function(){
				var deferred = $.Deferred();
				var self = this;
				var ids = {ids: []};
				self._relatedTags = [];
				if(!self._notes.getAll().length){
					deferred.resolve();
					return deferred.promise();
				}
				$.each(self._notes.getAll(), function(index, value){
					ids.ids.push(value.id);
				});
				$.ajax({
					url: self._baseUrl,
					method: 'POST',
					contentType: 'application/json',
					data: JSON.stringify(ids)
				}).done(function (tags) {
					self._relatedTags = tags;
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
			/**
			 * AJAX call.
			 * Deletes the object relation of the given noteId and the tag.title.
			 * @param id
			 * @param title
             * @returns $.Deferred()
             */
			unTag: function (id, title) {
				var deferred = $.Deferred();
				var self = this;
				var untag = {id: id, title: title};
				$.ajax({
					url: OC.generateUrl('/apps/nextnotes/untag'),
					contentType: 'application/json',
					method: 'POST',
					data: JSON.stringify(untag)
				}).done(function () {
					//update object
					var i = self._relatedTags[id].indexOf(title);
					if(i != -1) {
						self._relatedTags[id].splice(i, 1);
					}
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
			/**
			 * AJAX call.
			 * Deletes a Tag completely (including its object relations if existing)
			 * @param title
             * @returns $.Deferred()
             */
			deleteTag: function (title) {
				var deferred = $.Deferred();
				var self = this;
				var deletetag = {title: title};
				$.ajax({
					url: OC.generateUrl('/apps/nextnotes/deletetag'),
					contentType: 'application/json',
					method: 'POST',
					data: JSON.stringify(deletetag)
				}).done(function () {
					$.when(self.loadAll()).done(function(){
						deferred.resolve();
					});
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
			/**
			 * AJAX call.
			 * Loads all available tags (also the ones without note relation).
			 * @returns $.Deferred()
             */
			loadAvailableTags: function(){
				var deferred = $.Deferred();
				var self = this;
				$.get(self._baseUrl).done(function (tags) {
					self._availableTags = tags;
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
			/**
			 * Getter for Available Tags
			 * @returns {Array}
             */
			getAvailableTags: function(){
				var self = this;
				return self._availableTags;
			},
			/**
			 * Getter for tags of a specific note
			 * @param id
             * @returns {Array}
             */
			getRelatedTags: function(id){
				var self = this;
				return self._relatedTags[id];
			},
			/**
			 * Getter for all related tags for notes
			 * @returns {Array}
             */
			getAllRelatedTags: function(){
				var self = this;
				return self._relatedTags;
			},
			/**
			 * Initialize function and update function.
			 * @returns $.Deferred()
             */
			loadAll: function(){
				var deferred = $.Deferred();
				var self = this;
				$.when(self.loadRelatedTags(),self.loadAvailableTags()).done(function(){
					deferred.resolve();
				}).fail(function(){
					deferred.reject();
				});
				return deferred.promise();
			}
		};
		
		/**
		 * Constructor of the Notes object.
		 * This object holds all our notes.
		 * @param baseUrl
         * @constructor
         */
		var Notes = function (baseUrl) {
			this._baseUrl = baseUrl;
			this._notes = [];
			this._activeNote = undefined;
		};

		/**
		 * Class prototype for Notes. Following functions are available:
		 * @type {{load: Notes.load, setActiveUndefined: Notes.setActiveUndefined,
		 * getActive: Notes.getActive, removeActive: Notes.removeActive, create: Notes.create,
		 * getAll: Notes.getAll, loadAll: Notes.loadAll, search: Notes.search,
		 * updateActive: Notes.updateActive}}
         */
		Notes.prototype = {
			/**
			 * Load (activate) specific note and deactivate others.
			 * @param id
             */
			load: function (id) {
				var self = this;
				this._notes.forEach(function (note) {
					if (note.id === id) {
						note.active = true;
						self._activeNote = note;
					} else {
						note.active = false;
					}
				});
			},
			/**
			 * Set active note to undefined
 			 */
			setActiveUndefined: function(){
				this._activeNote = undefined;
			},
			/**
			 * Get active note.
			 * @returns {undefined|note}
             */
			getActive: function () {
				var self = this;
				return self._activeNote;
			},
			/**
			 * AJAX call.
			 * Delete the currently active note.
			 * @returns $.Deferred()
             */
			removeActive: function () {
				var index;
				var deferred = $.Deferred();
				var id = this._activeNote.id;
				this._notes.forEach(function (note, counter) {
					if (note.id === id) {
						index = counter;
					}
				});
				if (index !== undefined) {
					// delete cached active note if necessary
					if (this._activeNote === this._notes[index]) {
						delete this._activeNote;
					}
					this._notes.splice(index, 1);
					$.ajax({
						url: this._baseUrl + '/' + id,
						method: 'DELETE'
					}).done(function () {
						deferred.resolve();
					}).fail(function () {
						deferred.reject();
					});
				} else {
					deferred.reject();
				}
				return deferred.promise();
			},
			/**
			 * AJAX call.
			 * Create the note. Note object has to look like this:
			 * note = {	title: title, content: content }
			 * @param note
             * @returns $.Deferred()
             */
			create: function (note) {
				var deferred = $.Deferred();
				var self = this;
				$.ajax({
					url: this._baseUrl,
					method: 'POST',
					contentType: 'application/json',
					data: JSON.stringify(note)
				}).done(function (note) {
					self._notes.push(note);
					self._activeNote = note;
					self.load(note.id);
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
			/**
			 * Get all Notes.
			 * @returns {Array}
             */
			getAll: function () {
				var self = this;
				return self._notes;
			},
			/**
			 * AJAX call.
			 * Loads all notes. Initial call.
			 * @returns $.Deferred()
             */
			loadAll: function () {
				var deferred = $.Deferred();
				var self = this;
				$.get(this._baseUrl).done(function (notes) {
					self._notes = notes;
					if(self.getActive() !== undefined){
						self.load(self.getActive().id);
					}
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
			/**
			 * AJAX call.
			 * Loads all notes for the given query string. Data has to be provided like following:
			 * search = { query: 'text'	}
			 * @param data
             * @returns $.Deferred()
             */
			search: function(data){
				var deferred = $.Deferred();
				var self = this;
				$.ajax({
					url: this._baseUrl+'/search',
					method: 'POST',
					contentType: 'application/json',
					data: JSON.stringify(data)
				}).done(function (notes) {
					self._notes = notes;
					if(self.getActive() !== undefined){
						self.load(self.getActive().id);
					}
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
			/**
			 * AJAX call.
			 * Updates the currently active note with the given title and content.
			 * @param title
			 * @param content
             * @returns {*}
             */
			updateActive: function (title, content) {
				var note = this.getActive();
				note.title = title;
				note.content = content;
				return $.ajax({
					url: this._baseUrl + '/' + note.id,
					method: 'PUT',
					contentType: 'application/json',
					data: JSON.stringify(note)
				});
			}
		};
		
		/**
		 * Constructor of the View object.
		 * This will update the different parts of the html.
		 * @param notes
		 * @param tags
         * @constructor
         */
		var View = function (notes, tags) {
			this._notes = notes;
			this._tags = tags;
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
					simplemde.value(self._notes.getActive().content);
				}else{
					simplemde.value('');
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
		/**
		 * Create the objects and the editor
         */
		var notes = new Notes(OC.generateUrl('/apps/nextnotes/notes'));
		var tags = new Tags(notes);
		var view = new View(notes, tags);
		/** global: SimpleMDE */
		var simplemde = new SimpleMDE({
			autoDownloadFontAwesome: false,
			autofocus: true,
			element: $('#nextnotes-textarea')[0],
			forceSync: false,
			placeholder: t('nextnotes','Type your note here...'),
			spellChecker: false,
			shortcuts: {
				"toggleFullScreen": null
			},
			toolbar: [
				{//clear the content of the editor and deactivate the active note.
					name: "new",
					action: function () {
						view._notes.setActiveUndefined();
						view.render();
					},
					className: "fa fa-file-text-o no-disable",
					title: t("nextnotes", "New")
				},
				{//create or update a note
					name: "safe",
					action: function () {
						var content = simplemde.value();
						var title = content.split('\n')[0]; // first line is the title
						//if no active note available, create a new one
						if(view._notes.getActive() === undefined){
							var note = {
								title: title,
								content: content
							};
							view._notes.create(note).done(function() {
								view.render();
							}).fail(function () {
								OC.Notification.showTemporary(t('nextnotes','Could not create note.'), {
									timeout: 10,
									isHTML: false
								});
							});
						}else{//if active note available, update it.
							$.when(view._notes.updateActive(title, content)).done(function () {
								view.render();
							}).fail(function () {
								OC.Notification.showTemporary(t('nextnotes','Could not update note. Note not found.'), {
									timeout: 10,
									isHTML: false
								});
							});
						}
					},
					className: "fa fa-save no-disable",
					title: t("nextnotes", "Safe")
				},
				// following options create the buttons for the editor toolbar
				{
					name: "undo",
					/** global: SimpleMDE */
					action: SimpleMDE.undo,
					className: "fa fa-undo",
					title: t("nextnotes", "Undo")
				},
				{
					name: "redo",
					action: SimpleMDE.redo,
					className: "fa fa-repeat",
					title: t("nextnotes", "Redo")
				},
				"|",
				{
					name: "bold",
					action: SimpleMDE.toggleBold,
					className: "fa fa-bold",
					title: t("nextnotes", "Bold")
				},
				{
					name: "italic",
					action: SimpleMDE.toggleItalic,
					className: "fa fa-italic",
					title: t("nextnotes", "Italic")
				},
				{
					name: "strikethrough",
					action: SimpleMDE.toggleStrikethrough,
					className: "fa fa-strikethrough",
					title: t("nextnotes", "Strikethrough")
				},
				{
					name: "heading",
					action: SimpleMDE.toggleHeadingSmaller,
					className: "fa fa-header",
					title: t("nextnotes", "Heading")
				},
				"|",
				{
					name: "code",
					action: SimpleMDE.toggleCodeBlock,
					className: "fa fa-code",
					title: t("nextnotes", "Code")
				},
				{
					name: "quote",
					action: SimpleMDE.toggleBlockquote,
					className: "fa fa-quote-left",
					title: t("nextnotes", "Quote")
				},
				{
					name: "unordered-list",
					action: SimpleMDE.toggleUnorderedList,
					className: "fa fa-list-ul",
					title: t("nextnotes", "Generic List")
				},
				{
					name: "ordered-list",
					action: SimpleMDE.toggleOrderedList,
					className: "fa fa-list-ol",
					title: t("nextnotes", "Numbered List")
				},
				"|",
				{
					name: "link",
					action: SimpleMDE.drawLink,
					className: "fa fa-link",
					title: t("nextnotes", "Create Link")
				},
				{
					name: "image",
					action: SimpleMDE.drawImage,
					className: "fa fa-picture-o",
					title: t("nextnotes", "Insert Image")
				},
				{
					name: "table",
					action: SimpleMDE.drawTable,
					className: "fa fa-table",
					title: t("nextnotes", "Insert Table")
				},
				"|",
				{
					name: "preview",
					action: SimpleMDE.togglePreview,
					className: "fa fa-eye no-disable",
					title: t("nextnotes", "Toggle Preview")
				},
				"|",
				{
					name: "guide",
					action: function(){
						window.open('https://simplemde.com/markdown-guide', '_blank');
					},
					className: "fa fa-question-circle",
					title: t("nextnotes", "Markdown Guide")
				}
			]
		});
		/**
		 * Initialize the app
		 */
		notes.loadAll().done(function () {
			tags.loadAll().done(function(){
				view.render();
			}).fail(function(){
				OC.Notification.showTemporary(t('nextnotes','Could not load tags.'), {
					timeout: 10,
					isHTML: false
				});
			});
		}).fail(function () {
			OC.Notification.showTemporary(t('nextnotes','Could not load notes.'), {
				timeout: 10,
				isHTML: false
			});
		});
	});
	/** global: OC */
})(OC, window, jQuery);