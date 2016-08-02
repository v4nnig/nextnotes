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

use OCA\NextNotes\Service\TagService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Controller;


/**
 * Class TagController
 * @package OCA\NextNotes\Controller
 */
class TagController extends Controller {

    private $service;
    private $userId;

    use Errors;

    public function __construct($AppName, IRequest $request, TagService $tagService, $UserId){
        parent::__construct($AppName, $request);
        $this->userId = $UserId;
        $this->service = $tagService;
    }

    /**
     * Get all possible tags for current user.
     * @NoAdminRequired
     * @return DataResponse
     */
    public function index(){
        return $this->service->getTagList();
    }

    /**
     * Get all tags for one note.
     * @NoAdminRequired
     * @param int $id
     * @return DataResponse
     */
    public function show($id) {
        return $this->handleNotFound(function () use ($id){
           return $this->service->findAll($id);
        });
    }

    /**
     * Create and relate tag to note.
     * @NoAdminRequired
     * @param int $id
     * @param string $title
     * @return DataResponse
     */
    public function create($id, $title){
        return $this->service->createTag($id, $title);
    }

    /**
     * Delete Tag (untag) for given note.
     * @NoAdminRequired
     * @param int $id
     * @param string $title
     * @return DataResponse
     */
    public function remove($id, $title){
        return $this->handleNotFound(function () use ($id, $title){
           return $this->service->unTag($id, $title);
        });
    }

    /**
     * Delete Tag completely from DB
     * @param $title
     * @return DataResponse
     */
    public function delete($title){
        return $this->handleNotFound(function () use ($title){
           return $this->service->delete($title);
        });
    }

}