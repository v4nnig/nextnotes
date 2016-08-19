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
namespace OCA\NextNotes\Tests\Controller;

use OCA\NextNotes\Controller\TagApiController;

require_once __DIR__ . '/TagControllerTest.php';

class TagApiControllerTest extends TagControllerTest {

	public function setUp() {
		parent::setUp();
		$this->controller = new TagApiController(
			'nextnotes', $this->request, $this->service, $this->userId
		);
	}

}