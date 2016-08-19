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
	/** global: OCA */
	if (!OCA.NextNotes) {
		/**
		 * @namespace
		 * global: OCA
		 */
		OCA.NextNotes = {};
	}
	/**
	 * OCA.NextNotes.App
	 * Integrates all necessary objects to build the app.
	 * @type {{initialize: OCA.NextNotes.App.initialize, createEditor: OCA.NextNotes.App.createEditor}}
	 * global: OCA
	 */
	OCA.NextNotes.App = {
		/**
		 * Initialize function. Gets all things together.
		 */
		initialize: function(){
			var self = this;
			// Create the Notes Object
			/** global: OCA */
			this._notes = new OCA.NextNotes.Notes(OC.generateUrl('/apps/nextnotes/notes'));
			// Create the Tags Object
			/** global: OCA */
			this._tags = new OCA.NextNotes.Tags(this._notes);
			// Create the Editor
			this.createEditor();
			//Initialize the app
			self._notes.loadAll().done(function () {
				self._tags.loadAll().done(function(){
					self._view.render();
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
			// Create the View Object
			/** global: OCA */
			this._view = new OCA.NextNotes.View(this._notes, this._tags, this._simplemde);
		},
		/**
		 * Create the Editor (SimpleMDE)
		 */
		createEditor: function(){
			var self = this;
			/** global: SimpleMDE */
			this._simplemde = new SimpleMDE({
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
							self._view._notes.setActiveUndefined();
							self._view.render();
						},
						className: "fa fa-file-text-o no-disable",
						title: t("nextnotes", "New")
					},
					{//create or update a note
						name: "safe",
						action: function () {
							var content = self._simplemde.value();
							var title = content.split('\n')[0]; // first line is the title
							//if no active note available, create a new one
							if(self._view._notes.getActive() === undefined){
								var note = {
									title: title,
									content: content
								};
								self._view._notes.create(note).done(function() {
									self._view.render();
								}).fail(function () {
									OC.Notification.showTemporary(t('nextnotes','Could not create note.'), {
										timeout: 10,
										isHTML: false
									});
								});
							}else{//if active note available, update it.
								$.when(self._view._notes.updateActive(title, content)).done(function () {
									self._view.render();
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
		}
	};
	/**
	 * Init the App
	 */
	$(document).ready(function () {
		/** global: OCA */
		OCA.NextNotes.App.initialize();
	});
/** global: OC */
})(OC, window, jQuery);