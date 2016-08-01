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
namespace OCA\NextNotes\Controller;

use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\ITagManager;


class TagApiController extends Controller {

    private $userId;
    private $tagmanager;

    use Errors;

    public function __construct($AppName, IRequest $request, ITagManager $tagManager, $UserId){
        parent::__construct($AppName, $request);
        $this->userId = $UserId;
        $this->tagmanager = $tagManager->load('nextnotes',array(),true,$this->userId);
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     * @param int $noteId
     * @return JSONResponse
     */
    public function getTagsForNote($noteId) {
        $noteIds = array($noteId);
        $tags = $this->tagmanager->getTagsForObjects($noteIds);
        return new JSONResponse($tags);
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     * @param string $title
     * @param int $noteId
     * @return JSONResponse
     */
    public function createTagForNote($title, $noteId) {
        $tag = $this->createTag($title);
        $result = $this->tagmanager->tagAs($noteId, $title);
        return new JSONResponse($result);
    }

    /**
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     * @param string $title
     * @return JSONResponse
     */
    public function createTag($title) {
        $result = $this->tagmanager->add($title);
        return new JSONResponse($result);
    }

}