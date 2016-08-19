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
use OCP\ILogger;
use OCP\Util;


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
     * @var ILogger
     */
    private $logger;

    /**
     * NoteService constructor.
     * @param NoteMapper $mapper
     * @param TagService $tagService
     * @param ILogger $logger
     */
    public function __construct(NoteMapper $mapper, TagService $tagService, ILogger $logger) {
        $this->mapper = $mapper;
        $this->service = $tagService;
        $this->logger = $logger;
    }


    /**
     * Get all notes from DB
     * @param string $userId
     * @return \OCP\AppFramework\Db\Entity|null
     */
    public function findAll($userId) {
        try {
            $notes = $this->mapper->findAll($userId);
            $this->logger->debug('Fetch notes: '.json_encode($notes), ['app' => 'nextnotes']);
            return $notes;
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Handle the possible thrown Exceptions from all methods of this class.
     * @param Exception $e
     * @throws NotFoundException
     */
    private function handleException($e) {
        $this->logger->logException($e, ['app' => 'nextnotes', 'message' => 'Exception during note service function processing']);
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException ||
            $e instanceof WrongCallException) {
            throw new NotFoundException($e->getMessage());
        }else {
            throw $e;
        }
    }

    /**
     * Get a specific note for a given id from the DB
     * @param integer $id
     * @param string $userId
     * @return \OCP\AppFramework\Db\Entity|null
     * @throws NotFoundException
     */
    public function find($id, $userId) {
        try {
            $note = $this->mapper->find($id, $userId);
            $this->logger->debug('Fetch note for id '.$id.': '.json_encode($note), ['app' => 'nextnotes']);
            return $note;
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Create a note with the given title and content
     * @param string $title
     * @param string $content
     * @param string $userId
     * @return \JsonSerializable
     * @throws WrongCallException
     */
    public function create($title, $content, $userId) {
        try {
            // if($title) empty then do not create note
            if (strlen($title) === 0) {
                throw new WrongCallException("Could not create note. Empty title.");
            }
            $note = new Note();
            $note->setTitle($title);
            $note->setContent($content);
            $note->setUserId($userId);
            $object = $this->mapper->insert($note);
            $this->logger->debug('Created note: '.json_encode($object), ['app' => 'nextnotes']);
            return $object;
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Update a note for the given id with title and content
     * @param integer $id
     * @param string $title
     * @param string $content
     * @param string $userId
     * @return \JsonSerializable
     * @throws WrongCallException
     */
    public function update($id, $title, $content, $userId) {
        try {
            if (strlen($title) === 0) {
                throw new WrongCallException('Could not update note. Empty title.');
            }
            $note = $this->mapper->find($id, $userId);
            $note->setTitle($title);
            $note->setContent($content);
            $object = $this->mapper->update($note);
            $this->logger->debug('Updated note: '.json_encode($object), ['app' => 'nextnotes']);
            return $object;
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Delete a note for the given id
     * @param integer $id
     * @param string $userId
     * @return \OCP\AppFramework\Db\Entity|null
     * @throws NotFoundException
     */
    public function delete($id, $userId) {
        try {
            //first delete all tag relations
            $this->service->purgeObject($id);
            //now delete the note
            $note = $this->mapper->find($id, $userId);
            $this->mapper->delete($note);
            $this->logger->debug('Deleted note: '.json_encode($note), ['app' => 'nextnotes']);
            return $note;
        } catch (Exception $e) {
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
     * @param string $query
     * @param string $userId
     * @return \OCP\AppFramework\Db\Entity|null
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

            if (!empty($hashtags[0])) {// tag search required
                //remove the tag strings from the query term
                $query = str_replace($hashtags[0], '', $query);
                // array of normalized tags
                $tags = str_replace('#', '', $hashtags[0]);
                //check if any empty string is in the tag array
                $tags = array_filter($tags, function($value) {
                    return $value !== '' AND $value !== ' ';
                });
                if (!empty($tags)) {
                    $tagSearch = true;
                }
            }
            $terms = preg_split('/\s+/', $query);
            $terms = array_filter($terms, function($value) {
                return $value !== '' AND $value !== ' ';
            });
            
            if (!empty($terms) AND !$tagSearch) { // fulltext search no tags
                $result = $this->mapper->fulltextSearchWithoutTagFilter($terms, $userId);
                $this->logger->debug('Fulltext search without tag filter. Result for search terms: '.json_encode($terms).': '.json_encode($result), ['app' => 'nextnotes']);
                return $result;
            }elseif (empty($terms) AND $tagSearch AND isset($tags)) { // tag search no fulltext
                $result = $this->mapper->tagSearch($tags, $userId);
                $this->logger->debug('Tag search. Result for searched tags: '.json_encode($tags).': '.json_encode($result), ['app' => 'nextnotes']);
                return $result;
            }elseif (!empty($terms) AND $tagSearch AND isset($tags)) {// fulltext search with tags
                $result = $this->mapper->fulltextSearchWithTagFilter($terms, $tags, $userId);
                $this->logger->debug('Fulltext search with tag filter. Result for search terms: '.json_encode($terms).', filtered with following tags: '.json_encode($tags).': '.json_encode($result), ['app' => 'nextnotes']);
                return $result;
            }else {
                throw new NotFoundException('No Notes found.');
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

	/**
     * For Hook post_deleteUser: deletes all notes of a specific user.
     * @param $userId
     * @throws NotFoundException
     */
    public function deleteNotesForUser($userId) {
        try {
            $this->mapper->deleteAllForUser($userId);
            $this->logger->debug('DeleteUser hook. Deleted all notes for user: '.$userId, ['app' => 'nextnotes']);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

	/**
     * Creates the first note of a user (post create) -> Introduction Note
     * @param $userId
     * @throws NotFoundException
     */
    public function createIntroNoteForUser($userId) {
        try {
            $note = new Note();
            $note->setTitle('# Welcome to Next Notes!');
            $note->setContent('# Welcome to Next Notes!');
            $note->setUserId($userId);
            $this->mapper->insert($note);
            $this->logger->debug('CreateUser hook. Created the introduction note for user: '.$userId, ['app' => 'nextnotes']);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

}