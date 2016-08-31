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


use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\NextNotes\Service\NoteService;

/**
 * Class NoteController
 * @package OCA\NextNotes\Controller
 */
class NoteController extends Controller {

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
     * NoteController constructor.
     * @param string $AppName
     * @param IRequest $request
     * @param NoteService $service
     * @param $UserId
     */
    public function __construct($AppName, IRequest $request,
                                NoteService $service, $UserId) {
        parent::__construct($AppName, $request);
        $this->service = $service;
        $this->userId = $UserId;
    }

    /**
     * Get all notes
     * @NoAdminRequired
     *
     * @return DataResponse
     */
    public function index() {
        return $this->handleNotFound(function() {
            return $this->service->findAll($this->userId);
        });
    }

    /**
     * Get a specific note for a given id
     * @NoAdminRequired
     *
     * @param int $id
     * @return DataResponse
     */
    public function show($id) {
        return $this->handleNotFound(function() use ($id) {
            return $this->service->find($id, $this->userId);
        });
    }

    /**
     * Create a note with the given title and content
     * @NoAdminRequired
     *
     * @param string $title
     * @param string $content
     * @return DataResponse
     */
    public function create($title, $content) {
        return $this->handleNotFound(function() use ($title, $content){
            return $this->service->create($title, $content, $this->userId);
        });
    }

    /**
     * Update a note for the given id with title and content
     * @NoAdminRequired
     *
     * @param int $id
     * @param string $title
     * @param string $content
     * @return DataResponse
     */
    public function update($id, $title, $content) {
        return $this->handleNotFound(function() use ($id, $title, $content) {
            return $this->service->update($id, $title, $content, $this->userId);
        });
    }

    /**
     * Delete a note for the given id.
     * @NoAdminRequired
     *
     * @param int $id
     * @return DataResponse
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
     * @NoAdminRequired
     *
     * @param string $query
     * @return DataResponse
     */
    public function search($query) {
        return $this->handleNotFound(function() use ($query) {
            return $this->service->search($query, $this->userId);
        });
    }

}