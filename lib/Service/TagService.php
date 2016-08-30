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
use OCA\NextNotes\Db\NoteMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\ILogger;
use OCP\ITags;


/**
 * Class TagService
 * @package OCA\NextNotes\Service
 */
class TagService {

    /**
     * @var \OCP\ITags $tagM
     */
    private $tagM;

	/**
	 * @var NoteMapper $noteMapper
	 */
	private $noteMapper;

	/**
	 * @var string $userId
	 */
	private $userId;

	/**
     * @var ILogger $logger
     */
    private $logger;

    /**
     * TagService constructor.
     * @param ITags $tagManager
     * @param ILogger $logger
     */
    public function __construct(ITags $tagManager, NoteMapper $noteMapper, $UserID, ILogger $logger) {
        $this->tagM = $tagManager;
        $this->logger = $logger;
		$this->noteMapper = $noteMapper;
		$this->userId = $UserID;
    }

	/**
	 * Handle the possible thrown Exceptions from all methods of this class.
	 * @param Exception $e
	 * @throws Exception | NotFoundException
	 */
    private function handleException($e) {
        $this->logger->logException($e, ['app' => 'nextnotes', 'message' => 'Exception during tag service function processing']);
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException ||
            $e instanceof NotChangeException ||
            $e instanceof WrongCallException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }

    /**
     * Find all tags related to the given note id.
     * Returns an array of tags with note id as key in following form:
     * [
     *   1 => array('First tag', 'Second tag'),
     *   4 => array('Second tag'),
     *   16 => array('Second tag', 'Third tag'),
     * ]
     * @param array $ids
     * @return JSONResponse
     * @throws NotFoundException
     */
    public function findAll($ids) {
        try {
            if (!isset($ids) OR empty($ids)) {throw new WrongCallException('WRONG ARGUMENTS'); }
            $tags = $this->tagM->getTagsForObjects($ids);
            if ($tags !== false) {
                if (!empty($tags)) {
                    $this->logger->debug('Fetch tags for ids: '.json_encode($tags), ['app' => 'nextnotes']);
                    return $tags;
                }
                return array();
            }
            throw new NotFoundException('Anything went wrong');
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Gets all tags for a specific user in following form:
     * array('First tag', 'Second tag', 'Third tag', ... , 'Last tag')
     * @return array
     * @throws NotFoundException
     */
    public function getTagList() {
        try {
            $tags = $this->tagM->getTags();
            $this->logger->debug('Fetch all tags: '.json_encode($tags), ['app' => 'nextnotes']);
            $result = array();
            foreach ($tags as $tag) {
                array_push($result, $tag['name']);
            }
            return $result;
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Create a tag for the given noteId and tagTitle.
     * @param integer $noteId
     * @param string $title
     * @return DataResponse
     * @throws NotFoundException
     */
    public function createTag($noteId, $title) {
        try {
            if (!isset($noteId) OR !isset($title) OR $title === 'undefined' OR $noteId === 'undefined' OR strpos($title, ',') !== false OR strpos($title, '#') !== false) {
            	throw new WrongCallException('WRONG ARGUMENTS');
            }
			$this->findNote($noteId, $this->userId);
            if ($this->tagM->tagAs($noteId, $title)) {
                $this->logger->debug('Tag created: '.$title, ['app' => 'nextnotes']);
                return new DataResponse(array());
            } else {
                throw new NotChangeException('Cannot create tag.');
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Untag: delete the obejct relation between given noteId and given tagtitle.
     * @param integer $noteId
     * @param string $title
     * @return DataResponse
     * @throws NotFoundException
     */
    public function unTag($noteId, $title) {
        try {
            if (!isset($noteId) OR !isset($title) OR $title === 'undefined' OR $noteId === 'undefined') {throw new WrongCallException('WRONG ARGUMENTS'); }
            if ($this->tagM->unTag($noteId, $title)) {
                $this->logger->debug('Untagged '.$title.' for note '.$noteId, ['app' => 'nextnotes']);
                return new DataResponse(array());
            } else {
                throw new NotChangeException('Cannot untag.');
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * If a note gets deleted, the object relation has to be removed.
     * @param integer $noteId
     * @return DataResponse
     * @throws NotFoundException
     */
    public function purgeObject($noteId) {
        try {
            if (!isset($noteId) OR $noteId === 'undefined') {throw new WrongCallException('WRONG ARGUMENTS'); }
            if ($this->tagM->purgeObjects(array($noteId))) {
                $this->logger->debug('Purged tags for note '.$noteId, ['app' => 'nextnotes']);
                return new DataResponse(array());
            } else {
                throw new NotFoundException('Could not purge.');
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Delete all object relations and the tag itself.
     * @param $titles
     * @return DataResponse
     * @throws NotFoundException
     */
    public function delete($titles) {
        try {
            if (!isset($titles) OR $titles === 'undefined') {throw new WrongCallException('WRONG ARGUMENTS'); }
            if (!is_array($titles)) {
                $titles = array($titles);
            }
            if ($this->tagM->delete($titles)) {
                $this->logger->debug('Deleted tag: '.json_encode($titles), ['app' => 'nextnotes']);
                return new DataResponse(array());
            } else {
                throw new NotFoundException('Could not delete.');
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

	/**
	 * Tries to find the note for the given id and user. Throws a Custom Exception, if nothing found.
	 * @param $id
	 * @param $userId
	 * @throws DoesNotExistException
	 */
	private function findNote($id, $userId){
    	try {
			$this->noteMapper->find($id, $userId);
		}catch (Exception $e){
			throw new DoesNotExistException('No note found for the given id.');
		}
	}
    
}