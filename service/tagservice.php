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
use OCP\AppFramework\Http;
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
     * @param int $id
     * @return JSONResponse
     * @throws NotFoundException
     */
    public function findAll($id) {
        try{
            $tags = $this->tagM->getTagsForObjects(array($id));
            if ($tags !== false) {
                if (empty($tags)) {
                    throw new NotFoundException('No tags found for '.$id);
                }
                return $tags[$id];
            }
            throw new NotFoundException('No tags found for '.$id);
        }catch(Exception $e){
            $this->handleException($e);
        }
    }

    public function getTagList(){
        try{
            return $this->tagM->getTags();
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function createTag($noteId, $title){
        try{
            if ($this->tagM->tagAs($noteId,$title)){
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
                return new DataResponse(array());
            }else{
                throw new NotChangeException('Cannot untag.');
            }
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function purgeObject($noteId){
        try{
            if($this->tagM->purgeObjects(array($noteId))){
                return new DataResponse(array());
            }else{
                throw new NotFoundException('Could not purge.');
            }
        }catch(Exception $e){
            $this->handleException($e);
        }
    }

    public function delete($title){
        try{
            if($this->tagM->delete(array($title))){
                return new DataResponse(array());
            }else{
                throw new NotFoundException('Could not delete.');
            }
        }catch (Exception $e){
            $this->handleException($e);
        }
    }
}