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

use OCA\NextNotes\Controller\PageController;
use OCA\NextNotes\Tests\TestCase;
use OCP\AppFramework\Http\TemplateResponse;


class PageControllerTest extends TestCase {

	private $controller;

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();

		$this->controller = new PageController(
			'nextnotes', $request
		);
	}


	public function testIndex() {
		$result = $this->controller->index();

		$this->assertEquals('main', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}


}