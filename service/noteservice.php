<?php
/**
 * nextCloud - nextnotes
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Janis Koehr <janiskoehr@icloud.com>
 * @copyright Janis Koehr 2016
 */
namespace OCA\NextNotes\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\NextNotes\Db\Note;
use OCA\NextNotes\Db\NoteMapper;


/**
 * Class NoteService
 * @package OCA\NextNotes\Service
 */
class NoteService {

    /**
     * @var NoteMapper
     */
    private $mapper;
    /**
     * @var TagService
     */
    private $service;

    /**
     * NoteService constructor.
     * @param NoteMapper $mapper
     * @param TagService $tagService
     */
    public function __construct(NoteMapper $mapper, TagService $tagService){
        $this->mapper = $mapper;
        $this->service = $tagService;
    }


    /**
     * Get all notes from DB
     * @param $userId
     * @return \JsonSerializable
     */
    public function findAll($userId) {
        return $this->mapper->findAll($userId);
    }

    /**
     * Handle the possible thrown Exceptions from all methods of this class.
     * @param $e
     * @throws NotFoundException
     */
    private function handleException ($e) {
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException ||
            $e instanceof WrongCallException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }

    /**
     * Get a specific note for a given id from the DB
     * @param $id
     * @param $userId
     * @return \JsonSerializable
     * @throws NotFoundException
     */
    public function find($id, $userId) {
        try {
            return $this->mapper->find($id, $userId);
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Create a note with the given title and content
     * @param $title
     * @param $content
     * @param $userId
     * @return \JsonSerializable
     * @throws WrongCallException
     */
    public function create($title, $content, $userId) {
        // if($title) empty then do not create note
        if(strlen($title) === 0){
            throw new WrongCallException("Could not create note. Empty title.");
        }
        $note = new Note();
        $note->setTitle($title);
        $note->setContent($content);
        $note->setUserId($userId);
        return $this->mapper->insert($note);
    }

    /**
     * Update a note for the given id with title and content
     * @param $id
     * @param $title
     * @param $content
     * @param $userId
     * @return \JsonSerializable
     * @throws WrongCallException
     */
    public function update($id, $title, $content, $userId) {
        try {
            if(strlen($title) === 0){
                throw new WrongCallException('Could not update note. Empty title.');
            }
            $note = $this->mapper->find($id, $userId);
            $note->setTitle($title);
            $note->setContent($content);
            return $this->mapper->update($note);
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Delete a note for the given id
     * @param $id
     * @param $userId
     * @return \JsonSerializable
     * @throws NotFoundException
     */
    public function delete($id, $userId) {
        try {
            //first delete all tag relations
            $this->service->purgeObject($id);
            //now delete the note
            $note = $this->mapper->find($id, $userId);
            $this->mapper->delete($note);
            return $note;
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Search for notes with given query.
     * Query can contain different string parts, which trigger different
     * kind of search behavior:
     *
     * 1. Fulltext search excluding tags. => Given Query is normal string,
     * without the '#' (hashtag) character, which is reserved.
     * Example: 'foo bar foobar' => Searches for all apparels of foo, bar
     * and foobar in all notes' content.
     *
     * 2. Tag search only. => Given Query is a string,
     * which is surrounded by the reserved character '#' (hashtag).
     * Example: '#foo# #bar# #foobar#' => Searches for tags: foo, bar and foobar
     *
     * 3. Fulltext search including a tag filter. => Given Query is a string
     * containing normal elements and tag elements like in the previous two cases.
     * Example: 'foo bar #foobar#' => Searches for all apparels of foo and bar
     * and return only the notes which are also tagged with foobar.
     *
     * @param $query
     * @param $userId
     * @return \JsonSerializable
     * @throws NotFoundException
     */
    public function search($query, $userId) {
        try {
            //remove all unwanted signs from the query
            $query = filter_var($query, FILTER_SANITIZE_STRING);
            $tagSearch = false;
            // if not empty query else throw notfound

            // gets all tags (string between '#' signs) into a array.
            preg_match_all('/\#[\w\s\!\-\_\?\*\+\%\p{Sc}\xC0-\xD6\xD8-\xF6\xF8-\xFF]*\#/u', $query, $hashtags);

            if(!empty($hashtags[0])){// tag search required
                //remove the tag strings from the query term
                $query = str_replace($hashtags[0],'',$query);
                // array of normalized tags
                $tags = str_replace('#','',$hashtags[0]);
                //check if any empty string is in the tag array
                $tags = array_filter($tags, function($value) {
                    return $value !== '' AND $value !== ' ';
                });
                if(!empty($tags)){
                    $tagSearch = true;
                }
            }
            $terms   = preg_split('/\s+/', $query);
            $terms = array_filter($terms, function($value) {
                return $value !== '' AND $value !== ' ';
            });
            
            if(!empty($terms) AND !$tagSearch){ // fulltext search no tags
                return $this->mapper->fulltextSearchWithoutTagFilter($terms, $userId);
            }elseif (empty($terms) AND $tagSearch AND isset($tags)){ // tag search no fulltext
                return $this->mapper->tagSearch($tags, $userId);
            }elseif (!empty($terms) AND $tagSearch AND isset($tags)) {// fulltext search with tags
                return $this->mapper->fulltextSearchWithTagFilter($terms, $tags, $userId);
            }else{
                throw new NotFoundException('No Notes found.');
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

}