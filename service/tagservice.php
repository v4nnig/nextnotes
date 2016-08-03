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
        try{
            if(!isset($ids) OR empty($ids)){throw new NotFoundException('WRONG ARGUMENTS');}
            $tags = $this->tagM->getTagsForObjects($ids);
            if ($tags !== false) {
                if(!empty($tags)){
                    return $tags;
                }
                return array();
            }
            throw new NotFoundException('Anything went wrong');
        }catch(Exception $e){
            $this->handleException($e);
        }
    }

    /**
     * Gets all tags for a specific user in following form:
     * array('First tag', 'Second tag', 'Third tag', ... , 'Last tag')
     * @return array
     * @throws NotFoundException
     */
    public function getTagList(){
        try{
            $tags = $this->tagM->getTags();
            $result = array();
            foreach($tags as $tag){
                array_push($result, $tag['name']);
            }
            return $result;
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function createTag($noteId, $title){
        try{
            if(!isset($noteId) OR !isset($title) OR $title == 'undefined' OR $noteId == 'undefined'){throw new NotFoundException('WRONG ARGUMENTS');}
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
            if(!isset($noteId) OR !isset($title) OR $title == 'undefined' OR $noteId == 'undefined'){throw new NotFoundException('WRONG ARGUMENTS');}
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
            if(!isset($noteId) OR $noteId == 'undefined'){throw new NotFoundException('WRONG ARGUMENTS');}
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
            if(!isset($title) OR $title == 'undefined'){throw new NotFoundException('WRONG ARGUMENTS');}
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