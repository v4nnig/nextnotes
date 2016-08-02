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
// this notes object holds all our notes
		var Notes = function (baseUrl) {
			this._baseUrl = baseUrl;
			this._tagURL = OC.generateUrl('/apps/nextnotes/tags');
			this._notes = [];
			this._tags = [];
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
			},
			/* TAG specific functions */
			createTag: function (tag) {
				var deferred = $.Deferred();
				var self = this;
				$.ajax({
					url: self._tagURL,
					method: 'POST',
					contentType: 'application/json',
					data: JSON.stringify(tag)
				}).done(function () {
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			},
			loadTags: function(id){
				var deferred = $.Deferred();
				var self = this;
				$.get(self._tagURL+'/'+id).done(function (tags) {
					self._tags = tags;
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
					url: self._tagURL+'/'+id+'/'+title,
					method: 'DELETE'
				}).done(function () {
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
					url: self._tagURL+'/'+title,
					method: 'DELETE'
				}).done(function () {
					deferred.resolve();
				}).fail(function () {
					deferred.reject();
				});
				return deferred.promise();
			}
		};

// this will be the view that is used to update the html
		var View = function (notes) {
			this._notes = notes;
		};

		View.prototype = {
			renderContent: function () {
				if(this._notes.getActive() !== undefined){
					simplemde.value(this._notes.getActive().content);
				}else{
					simplemde.value('');
				}
			},
			renderNavigation: function () {
				var source = $('#navigation-tpl').html();
				var template = Handlebars.compile(source);
				var html = template({notes: this._notes.getAll()});

				$('#app-navigation ul').html(html);

				var self = this;
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
					self._notes.load(id);
					self.render();
					if(simplemde.isPreviewActive()){
						simplemde.togglePreview();
					}
				});

				//Safe Note
				$('#editor-toolbar > a:eq( 1 )').click();
			},

			render: function () {
				this.renderNavigation();
				this.renderContent();
				//this.renderInfoView();
			}
		};

		var notes = new Notes(OC.generateUrl('/apps/nextnotes/notes'));
		var view = new View(notes);
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
			view.render();
		}).fail(function () {
			alert(t('nextnotes','Could not load notes'));
		});
	});

})(OC, window, jQuery);