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
        $this->service->createTag(14,'Superb');
    }

    /**
     * @NoAdminRequired
     * @param string $id
     * @param string $title
     * @return \OCP\AppFramework\Http\DataResponse
     */
    public function create($id, $title){
        return $this->service->createTag($id, $title);
    }

}