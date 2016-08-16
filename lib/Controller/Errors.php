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

use Closure;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCA\NextNotes\Service\NotFoundException;


/**
 * Class Errors
 * @package OCA\NextNotes\Controller
 */
trait Errors {

    /**
     * handles the thrown Errors for all Controllers
     * and sends a DataResponse with the ErrorMessage of the service
     * @param Closure $callback
     * @return DataResponse
     */
    protected function handleNotFound (Closure $callback) {
        try {
            return new DataResponse($callback());
        } catch(NotFoundException $e) {
            $message = ['message' => $e->getMessage()];
            return new DataResponse($message, Http::STATUS_NOT_FOUND);
        }
    }

}