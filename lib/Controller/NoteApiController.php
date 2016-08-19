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
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;

use OCA\NextNotes\Service\NoteService;

/**
 * Class NoteApiController
 * @package OCA\NextNotes\Controller
 */
class NoteApiController extends ApiController {

    /**
     * @var NoteService
     */
    private $service;
    /**
     * @var string
     */
    private $userId;

    use Errors;

    /**
     * NoteApiController constructor.
     * @param string $AppName
     * @param IRequest $request
     * @param NoteService $service
     * @param string $userId
     */
    public function __construct($AppName, IRequest $request,
                                NoteService $service, $userId) {
        parent::__construct($AppName, $request);
        $this->service = $service;
        $this->userId = $userId;
    }

    /**
     * Get all notes
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function index() {
        return new DataResponse($this->service->findAll($this->userId));
    }

    /**
     * Get a specific note for a given id
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     * @param int $id
     * @return JSONResponse
     */
    public function show($id) {
        return $this->handleNotFound(function() use ($id) {
            return $this->service->find($id, $this->userId);
        });
    }

    /**
     * Create a note with the given title and content
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     * @param string $title
     * @param string $content
     * @return JSONResponse
     */
    public function create($title, $content) {
        return $this->handleNotFound(function() use ($title, $content) {
            return $this->service->create($title, $content, $this->userId);
        });
    }

    /**
     * Update a note for the given id with title and content
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     * @param int $id
     * @param string $title
     * @param string $content
     * @return JSONResponse
     */
    public function update($id, $title, $content) {
        return $this->handleNotFound(function() use ($id, $title, $content) {
            return $this->service->update($id, $title, $content, $this->userId);
        });
    }

    /**
     * Delete a note for the given id.
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     * @param int $id
     * @return JSONResponse
     */
    public function destroy($id) {
        return $this->handleNotFound(function() use ($id) {
            return $this->service->delete($id, $this->userId);
        });
    }

    /**
     * Search for notes with given query.
     * Query can contain different string parts,
     * which are defined at the service method:
     * OCA\NextNotes\NoteService::search()
     *
     * @CORS
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     * @param string $query
     * @return JSONResponse
     */
    public function search($query) {
        return $this->handleNotFound(function() use ($query) {
            return $this->service->search($query, $this->userId);
        });
    }

}