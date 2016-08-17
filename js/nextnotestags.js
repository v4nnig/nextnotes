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
	OCA.NextNotes.Tags = Tags;
})();
