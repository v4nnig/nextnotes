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
use OCP\AppFramework\Http\JSONResponse;
use OCP\JSON;


class NoteService {

    private $mapper;
    private $service;

    public function __construct(NoteMapper $mapper, TagService $tagService){
        $this->mapper = $mapper;
        $this->service = $tagService;
    }

    public function findAll($userId) {
        return $this->mapper->findAll($userId);
    }

    private function handleException ($e) {
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }

    public function find($id, $userId) {
        try {
            return $this->mapper->find($id, $userId);
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function create($title, $content, $userId) {
        // if($title) empty then do not create note
        if(strlen($title) == 0){
            throw new NotFoundException("Could not create note. Empty title.");
        }
        $note = new Note();
        $note->setTitle($title);
        $note->setContent($content);
        $note->setUserId($userId);
        return $this->mapper->insert($note);
    }

    public function update($id, $title, $content, $userId) {
        try {
            if(strlen($title) == 0){
                throw new NotFoundException('Could not update note. Empty title.');
            }
            $note = $this->mapper->find($id, $userId);
            $note->setTitle($title);
            $note->setContent($content);
            return $this->mapper->update($note);
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

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

    public function search($query, $userId) {
        try {
            //remove all unwanted signs from the query
            $query = filter_var($query, FILTER_SANITIZE_STRING);
            $tagSearchResult = [];
            $noteSearchResult = [];
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
            }elseif (empty($terms) AND $tagSearch){ // tag search no fulltext
                return $this->mapper->tagSearch($tags, $userId);
            }elseif (!empty($terms) AND $tagSearch) {// fulltext search with tags
                return $this->mapper->fulltextSearchWithTagFilter($terms, $tags, $userId);
            }else{
                throw new NotFoundException('No Notes found.');
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

}