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
	if (OCA.NextNotes) {
		OCA.NextNotes.Notes = Notes;
	}
})();