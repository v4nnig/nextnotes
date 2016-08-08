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
		var Tags = function (notes){
			this._baseUrl = OC.generateUrl('/apps/nextnotes/tags');
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
					$.when(self.loadAll()).done(function(){
						deferred.resolve();
					});
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
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
				var self = this;
				return self._availableTags;
			},
			getRelatedTags: function(id){
				var self = this;
				return self._relatedTags[id];
			},
			getAllRelatedTags: function(){
				var self = this;
				return self._relatedTags;
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
				var self = this;
				return self._activeNote;
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
				var self = this;
				return self._notes;
			},
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
				var view = [];
				var viewnotes = self._notes.getAll();
				var viewtags = self._tags.getAllRelatedTags();
				$.each(viewnotes, function(index,element){
					view.push({id: element.id, title: element.title, content: element.content, active: element.active});
					$.each(viewtags, function(i, e){
						if(element.id == i){
							view[index].tags = e;
						}
					});
				});
				var html = template({notes: view});

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

				var formSearchbox = $('form.nextnotes-searchbox');
				var searchbox = $('#nextnotes-searchbox');

				//destroy all registered events for the searchbox
				formSearchbox.off('submit.nextnotes');
				searchbox.off('keyup.nextnotes');

				// search for notes
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

				//Register the "x" for the search field
				if($('.clearInput').length){
					$('.clearicon').unwrap();
					$('.clearInput').remove();
				}
				$('.clearicon').clearIcon({"callback":function(){
					self.searchHelper();
				}});

				// load a note
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
						formatNoMatches: function(){
							return t('nextnotes', 'No matches.');
						},
						formatSearching: t('nextnotes', 'Searching...')
					});
					// INITIAL SELECTION
					select2Element.val(self._tags.getRelatedTags(self._notes.getActive().id)).trigger("change");
					// UNTAG EVENT
					select2Element.on("select2-removing", function(e){
						self._tags.unTag(self._notes.getActive().id, e.choice.id).done(function(){
							self.render();
						}).fail(function(){
							event.preventDefault();
							alert(t('nextnotes', 'Could not untag.'));
						});
					});
					// CREATE TAG EVENT
					select2Element.on("change", function(e) {
						if (e.added !== undefined){
							var create = {id: self._notes.getActive().id, title: e.added.text};
							$.when(self._tags.createTag(create)).done(function(){
								self.render();
							}).fail(function(){
								alert(t('nextnotes', 'Could not create tag.'));
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
			adoptTheming: function(){
				//Dynamic BackgroundColor for Tags
				var themeBackgroundColor = $('#header').css('background-color');
				//TODO: maybe there will be a optimization of theming nextcloud.. so there could be the possibility to get these values from the framework
				var themeColor = $('.header-appname').css('color');
				var style = $('<style>.select2-container-multi .select2-choices .select2-search-choice { background-color: '+themeBackgroundColor+'; color: '+themeColor+'; } .nextnotes-navigation-tag { background-color: '+themeBackgroundColor+'; color: '+themeColor+'; }</style>');
				$('html > head').append(style);
			},
			searchHelper: function(){
				var self = this;
				var searchbox = $('#nextnotes-searchbox');
				if(!searchbox.val()){
					$.when(self._notes.loadAll()).done(function(){
						self.renderNavigation();
					}).fail(function(){
						alert(t('nextnotes','Could not load notes.'));
					});
				}else {
					var search = {
						query: searchbox.val(),
					};
					$.when(self._notes.search(search)).done(function () {
						$.when(self._tags.loadAll()).done(function(){
							self.renderNavigation();
						});
					}).fail(function () {
						alert(t('nextnotes', 'Could not search for notes.'));
					});
				}
			},
			renderTagManager: function(){
				var self = this;
				var source = $('#settings-tpl').html();
				var template = Handlebars.compile(source);
				var html = template({tags: self._tags.getAvailableTags()});
				$('#app-settings-content').html(html);
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
			render: function () {
				this.renderContent();
				this.renderNavigation();
				this.renderInfoView();
				this.adoptTheming();
				this.renderTagManager();
			}
		};
		
		var notes = new Notes(OC.generateUrl('/apps/nextnotes/notes'));
		var tags = new Tags(notes);
		var view = new View(notes, tags);
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
							$.when(view._notes.updateActive(title, content)).done(function () {
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