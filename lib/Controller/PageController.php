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
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Controller;


/**
 * Class PageController
 * @package OCA\NextNotes\Controller
 */
class PageController extends Controller {

	/**
	 * PageController constructor.
	 * @param string $AppName
	 * @param IRequest $request
     */
	public function __construct($AppName, IRequest $request){
		parent::__construct($AppName, $request);
	}

	/**
	 * Index method for the internal Next Notes part.
	 * Responds the main template for Next Notes.
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return TemplateResponse
	 */
	public function index() {
		return new TemplateResponse('nextnotes', 'main');
	}



}