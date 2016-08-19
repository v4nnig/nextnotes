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

use OCA\NextNotes\Controller\TagController;
use OCA\NextNotes\Service\NotFoundException;
use OCA\NextNotes\Tests\TestCase;
use OCP\AppFramework\Http;



class TagControllerTest extends TestCase {
	protected $controller;
	protected $service;
	protected $userId = 'john';
	protected $request;

	public function setUp() {
		$this->request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$this->service = $this->getMockBuilder('OCA\NextNotes\Service\TagService')
			->disableOriginalConstructor()
			->getMock();
		$this->controller = new TagController(
			'nextnotes', $this->request, $this->service, $this->userId
		);
	}

	public function testIndex(){
		$tag = 'just check if this value is returned correctly';
		$this->service->expects($this->once())
			->method('getTagList')
			->will($this->returnValue($tag));

		$result = $this->controller->index();

		$this->assertEquals($tag, $result->getData());
		$this->assertEquals(Http::STATUS_OK, $result->getStatus());
	}

	public function testShow(){
		$tag = 'just check if this value is returned correctly';
		$ids = [1,2,3];
		$this->service->expects($this->once())
			->method('findAll')
			->with($this->equalTo($ids))
			->will($this->returnValue($tag));

		$result = $this->controller->show($ids);

		$this->assertEquals($tag, $result->getData());
		$this->assertEquals(Http::STATUS_OK, $result->getStatus());
	}

	public function testShowNotFound(){
		// test the correct status code if no tag is found
		$ids = [1,2,3];
		$this->service->expects($this->once())
			->method('findAll')
			->will($this->throwException(new NotFoundException()));

		$result = $this->controller->show($ids);

		$this->assertEquals(Http::STATUS_NOT_FOUND, $result->getStatus());
	}

	public function testCreate(){
		$tag = 'just check if this value is returned correctly';
		$this->service->expects($this->once())
			->method('createTag')
			->with($this->equalTo(1),
				$this->equalTo('title'))
			->will($this->returnValue($tag));

		$result = $this->controller->create(1,'title');

		$this->assertEquals($tag, $result->getData());
		$this->assertEquals(Http::STATUS_OK, $result->getStatus());
	}

	public function testCreateNotFound(){
		// test the correct status code if tag could not be created
		$this->service->expects($this->once())
			->method('createTag')
			->will($this->throwException(new NotFoundException()));

		$result = $this->controller->create(1, 'title');

		$this->assertEquals(Http::STATUS_NOT_FOUND, $result->getStatus());
	}

	public function testRemove(){
		$tag = 'just check if this value is returned correctly';
		$this->service->expects($this->once())
			->method('unTag')
			->with($this->equalTo(1),
				$this->equalTo('title'))
			->will($this->returnValue($tag));

		$result = $this->controller->remove(1,'title');

		$this->assertEquals($tag, $result->getData());
		$this->assertEquals(Http::STATUS_OK, $result->getStatus());
	}

	public function testRemoveNotFound(){
		// test the correct status code if tag could not be created
		$this->service->expects($this->once())
			->method('unTag')
			->will($this->throwException(new NotFoundException()));

		$result = $this->controller->remove(1, 'title');

		$this->assertEquals(Http::STATUS_NOT_FOUND, $result->getStatus());
	}

	public function testDelete(){
		$tag = 'just check if this value is returned correctly';
		$this->service->expects($this->once())
			->method('delete')
			->with($this->equalTo('title'))
			->will($this->returnValue($tag));

		$result = $this->controller->delete('title');

		$this->assertEquals($tag, $result->getData());
		$this->assertEquals(Http::STATUS_OK, $result->getStatus());
	}

	public function testDeleteNotFound(){
		// test the correct status code if tag could not be created
		$this->service->expects($this->once())
			->method('delete')
			->will($this->throwException(new NotFoundException()));

		$result = $this->controller->delete('title');

		$this->assertEquals(Http::STATUS_NOT_FOUND, $result->getStatus());
	}

}