/**
 * nextCloud - nextnotes
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Janis Koehr <janiskoehr@icloud.com>
 * @copyright Janis Koehr 2016
 */

(function (OC, window, $, undefined) {
	'use strict';
	$(document).ready(function () {
// this tags object holds all our tags
		var Tags = function (baseUrl, notes){
			this._baseUrl = baseUrl;
			this._notes = notes;
			this._availableTags = [];
			this._relatedTags = [];
		};

		Tags.prototype = {
			createTag: function (create) {
				var deferred = $.Deferred();
				var self = this;
				$.ajax({
					url: OC.generateUrl('/apps/nextnotes/tagging'),
					method: 'POST',
					contentType: 'application/json',
					data: JSON.stringify(create)
				}).done(function () {
					//update object
					self._relatedTags[create.id].push(create.title);
					//update available
					var i = self._availableTags.indexOf(create.title);
					if(i == -1){
						self._availableTags.push(create.title);
					}
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
			loadRelatedTags: function(){
				var deferred = $.Deferred();
				var self = this;
				var ids = {ids: []};
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
			unTag: function (id, title) {
				var deferred = $.Deferred();
				var self = this;
				$.ajax({
					url: self._baseUrl+'/'+id+'/'+title,
					method: 'DELETE'
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
			deleteTag: function (title) {
				var deferred = $.Deferred();
				var self = this;
				$.ajax({
					url: self._baseUrl+'/'+title,
					method: 'DELETE'
				}).done(function () {
					//update objects
					$.each(self._relatedTags, function(index, value){
						var i = self._relatedTags[index].indexOf(title);
						if(i != -1) {
							self._relatedTags[index].splice(i, 1);
						}
					});
					//update available
					var i = self._availableTags[id].indexOf(title);
					if(i != -1) {
						self._availableTags[id].splice(i, 1);
					}

					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
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
			getAvailableTags: function(){
				return this._availableTags;
			},
			getRelatedTags: function(id){
				return this._relatedTags[id];
			},
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
// this notes object holds all our notes
		var Notes = function (baseUrl) {
			this._baseUrl = baseUrl;
			this._notes = [];
			this._activeNote = undefined;
		};

		Notes.prototype = {
			/* NOTE specific functions. */
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
			setActiveUndefined: function(){
				this._activeNote = undefined;
			},
			getActive: function () {
				return this._activeNote;
			},
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
			getAll: function () {
				return this._notes;
			},
			loadAll: function () {
				var deferred = $.Deferred();
				var self = this;
				$.get(this._baseUrl).done(function (notes) {
					self._activeNote = undefined;
					self._notes = notes;
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
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

// this will be the view that is used to update the html
		var View = function (notes, tags) {
			this._notes = notes;
			this._tags = tags;
		};

		View.prototype = {
			renderContent: function () {
				var self = this;
				if(self._notes.getActive() !== undefined){
					simplemde.value(self._notes.getActive().content);
				}else{
					simplemde.value('');
				}
			},
			renderNavigation: function () {
				var self = this;
				var source = $('#navigation-tpl').html();
				var template = Handlebars.compile(source);
				var html = template({notes: self._notes.getAll()});

				$('#app-navigation ul').html(html);

				// show app menu
				$('#app-navigation .app-navigation-entry-utils-menu-button').click(function () {
					var entry = $(this).closest('.note');
					entry.find('.app-navigation-entry-menu').toggleClass('open');
				});

				// delete a note
				$('#app-navigation .note .delete').click(function () {
					var entry = $(this).closest('.note');
					entry.find('.app-navigation-entry-menu').removeClass('open');

					self._notes.removeActive().done(function () {
						self.render();
					}).fail(function () {
						alert(t('nextnotes','Could not delete note, not found'));
					});
				});

				// load a note
				$('#app-navigation .note > a').click(function () {
					var id = parseInt($(this).parent().data('id'), 10);
					$.when(self._notes.load(id)).done(self.render());
					if(simplemde.isPreviewActive()){
						simplemde.togglePreview();
					}
				});
			},
			renderInfoView: function(){
				var self = this;
				var inputElement = $('#nextnotesTagsInput');
				// remove
				inputElement.select2("destroy");
				inputElement.remove();
				// check if activeNote is available
				if(self._notes.getActive() !== undefined){
					// initialize
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
						formatNoMatches: function(term){
							return t('nextnotes', 'No matches.');
						},
						formatSearching: t('nextnotes', 'Searching...')
					});
					// INITIAL SELECTION
					select2Element.val(self._tags.getRelatedTags(self._notes.getActive().id)).trigger("change");
					// UNTAG EVENT
					select2Element.on("select2-removing", function(e){
						self._tags.unTag(self._notes.getActive().id,e.choice.id).done(function(){
							return true;
						}).fail(function(){
							event.preventDefault();
							return false;
						});
					});
					// CREATE TAG EVENT
					select2Element.on("change", function(e) {
						if (e.added !== undefined){
							var create = {id: self._notes.getActive().id, title: e.added.text};
							self._tags.createTag(create).done(function(){
								return true;
							}).fail(function(){
								alert(t('nextnotes', 'Could not create tag.'));
							});
						}
					});
					//TODO: maybe in "einstellungen" -> Completely delete a tag.
					//Dynamic BackgroundColor for Tags
					var themeBackgroundColor = $('#header').css('background-color');
					//TODO: maybe there will be a optimization of theming nextcloud.. so there could be the possibility to get these values from the framework
					var themeColor = $('.header-appname').css('color');
					var style = $('<style>.select2-container-multi .select2-choices .select2-search-choice { background-color: '+themeBackgroundColor+'; color: '+themeColor+'; }</style>');
					$('html > head').append(style);
				}
			},
			render: function () {
				this.renderNavigation();
				this.renderContent();
				this.renderInfoView();
			}
		};
		var notes = new Notes(OC.generateUrl('/apps/nextnotes/notes'));
		var tags = new Tags(OC.generateUrl('/apps/nextnotes/tags'), notes);
		var view = new View(notes, tags);
		var simplemde = new SimpleMDE({
			autoDownloadFontAwesome: false,
			autofocus: true,
			element: $('#nextnotes-textarea')[0],
			forceSync: false,
			placeholder: t('nextnotes','Type your note here...'),
			spellChecker: false,
			shortcuts: {
				"toggleFullScreen": null,
			},
			toolbar: [
				{
					name: "new",
					action: function () {
						view._notes.setActiveUndefined();
						view.render();
					},
					className: "fa fa-file-text-o no-disable",
					title: t("nextnotes", "New")
				},
				{
					name: "safe",
					action: function () {
						var content = simplemde.value();
						var title = content.split('\n')[0]; // first line is the title
						if(view._notes.getActive() === undefined){
							var note = {
								title: title,
								content: content
							};
							view._notes.create(note).done(function() {
								view.render();
							}).fail(function () {
								alert(t('nextnotes','Could not create note'));
							});
						}else{
							view._notes.updateActive(title, content).done(function () {
								view.render();
							}).fail(function () {
								alert(t('nextnotes','Could not update note, not found'));
							});
						}
					},
					className: "fa fa-save no-disable",
					title: t("nextnotes", "Safe")
				},
				{
					name: "undo",
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
		notes.loadAll().done(function () {
			tags.loadAll().done(function(){
				view.render();
			}).fail(function(){
				alert(t('nextnotes','Could not load tags'));
			});
		}).fail(function () {
			alert(t('nextnotes','Could not load notes'));
		});
	});

})(OC, window, jQuery);