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
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\ITagManager;


class TagService {

    /**
     * @var \OCP\ITagManager
     */
    private $tagM;

    public function __construct(ITagManager $tagManager){
        $this->tagM = $tagManager->load('nextnotes');
    }

    private function handleException ($e) {
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException ||
            $e instanceof NotChangeException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }

    /**
     * Find all tags related to the given note id.
     * Returns an array of tags with note id as key.
     * @param string $id
     * @return mixed
     * @throws NotFoundException
     */
    public function findAll($id) {
        try {
            $objIcs = array($id);
            $tags = $this->tagM->getTagsForObjects($objIcs);
            if ($tags !== false) {
                if (empty($tags)) {
                    throw new DoesNotExistException('No tags for this object found.');
                }
                //TODO: return JSON?
                return new JSONResponse(current($tags));
            }else{
                throw new DoesNotExistException('No tags for this object found.');
            }
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function getTagList(){
        try{
            return new JSONResponse($this->tagM->getTags());
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function createTag($noteId, $title){
        try{
            if ($this->tagM->tagAs($noteId,$title)){
                //TODO
                return new DataResponse(array());
            }else{
                throw new NotChangeException('Cannot create tag.');
            }
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function unTag($noteId, $title){
        try{
            if($this->tagM->unTag($noteId, $title)){
                //TODO
                return new DataResponse(array());
            }else{
                throw new NotChangeException('Cannot untag.');
            }
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }
}