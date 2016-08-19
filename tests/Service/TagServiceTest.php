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

namespace OCA\NextNotes\Tests\Service;

use OCA\NextNotes\Service\TagService;
use OCA\NextNotes\Tests\TestCase;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;


class TagServiceTest extends TestCase {

	private $tagM;
	private $service;
	private $logger;

	public function setUp() {
		$this->logger = $this->getMockBuilder('OCP\ILogger')
			->disableOriginalConstructor()
			->getMock();
		$this->tagM = $this->getMockBuilder('\OCP\ITags')
			->disableOriginalConstructor()
			->getMock();
		$this->service = new TagService($this->tagM, $this->logger);
	}

	public function testFindAll(){
		$tags = 'tags';
		$ids = [1,2];
		$this->tagM->expects($this->once())
			->method('getTagsForObjects')
			->with($this->equalTo($ids))
			->will($this->returnValue($tags));
		$result = $this->service->findAll($ids);
		$this->assertEquals('tags', $result);
	}

	public function testFindAllEmpty(){
		$tags = array();
		$ids = [1,2];
		$this->tagM->expects($this->once())
			->method('getTagsForObjects')
			->with($this->equalTo($ids))
			->will($this->returnValue($tags));
		$result = $this->service->findAll($ids);
		$this->assertEmpty($result);
	}

	/**
	 * @expectedException \OCA\NextNotes\Service\NotFoundException
	 */
	public function testFindAllNotFoundOne(){
		$ids = array();
		$this->service->findAll($ids);
	}

	/**
	 * @expectedException \OCA\NextNotes\Service\NotFoundException
	 */
	public function testFindAllNotFoundTwo(){
		$ids = ['1','2'];
		$this->tagM->expects($this->once())
			->method('getTagsForObjects')
			->with($this->equalTo($ids))
			->will($this->returnValue(false));
		$this->service->findAll($ids);
	}

	public function testGetTagList(){
		$tags = array(['id' => '1','name' => 'tag1'],['id' => '2','name' => 'tag2']);
		$this->tagM->expects($this->once())
			->method('getTags')
			->will($this->returnValue($tags));
		$result = $this->service->getTagList();
		$this->assertEquals(['tag1','tag2'], $result);
	}

	/**
	 * @expectedException \OCA\NextNotes\Service\NotFoundException
	 */
	public function testGetTagListNotFound(){
		$this->tagM->expects($this->once())
			->method('getTags')
			->will($this->throwException(new DoesNotExistException('')));
		$this->service->getTagList();
	}

	public function testCreateTag(){
		$this->tagM->expects($this->once())
			->method('tagAs')
			->with($this->equalTo(1),
				$this->equalTo('title'))
			->will($this->returnValue(true));
		$result = $this->service->createTag(1,'title');
		$this->assertEquals(Http::STATUS_OK, $result->getStatus());
	}

	/**
	 * @expectedException \OCA\NextNotes\Service\NotFoundException
	 */
	public function testCreateTagNotFoundOne(){
		$this->service->createTag(null,null);
	}

	/**
	 * @expectedException \OCA\NextNotes\Service\NotFoundException
	 */
	public function testCreateTagNotFoundTwo(){
		$this->tagM->expects($this->once())
			->method('tagAs')
			->with($this->equalTo(1),
				$this->equalTo('title'))
			->will($this->returnValue(false));
		$this->service->createTag(1,'title');
	}

	public function testUnTag(){
		$this->tagM->expects($this->once())
			->method('unTag')
			->with($this->equalTo(1),
				$this->equalTo('title'))
			->will($this->returnValue(true));
		$result = $this->service->unTag(1,'title');
		$this->assertEquals(Http::STATUS_OK, $result->getStatus());
	}

	/**
	 * @expectedException \OCA\NextNotes\Service\NotFoundException
	 */
	public function testUnTagNotFoundOne(){
		$this->service->unTag(null,null);
	}

	/**
	 * @expectedException \OCA\NextNotes\Service\NotFoundException
	 */
	public function testUnTagNotFoundTwo(){
		$this->tagM->expects($this->once())
			->method('unTag')
			->with($this->equalTo(1),
				$this->equalTo('title'))
			->will($this->returnValue(false));
		$this->service->unTag(1,'title');
	}

	public function testPurgeObject(){
		$this->tagM->expects($this->once())
			->method('purgeObjects')
			->with($this->equalTo(array(1)))
			->will($this->returnValue(true));
		$result = $this->service->purgeObject(1);
		$this->assertEquals(Http::STATUS_OK, $result->getStatus());
	}

	/**
	 * @expectedException \OCA\NextNotes\Service\NotFoundException
	 */
	public function testPurgeObjectNotFoundOne(){
		$this->service->purgeObject(null);
	}

	/**
	 * @expectedException \OCA\NextNotes\Service\NotFoundException
	 */
	public function testPurgeObjectNotFoundTwo(){
		$this->tagM->expects($this->once())
			->method('purgeObjects')
			->with($this->equalTo(array(1)))
			->will($this->returnValue(false));
		$this->service->purgeObject(1);
	}

	public function testDelete(){
		$titles = ['title1', 'title2'];
		$this->tagM->expects($this->once())
			->method('delete')
			->with($this->equalTo($titles))
			->will($this->returnValue(true));
		$result = $this->service->delete($titles);
		$this->assertEquals(Http::STATUS_OK, $result->getStatus());
	}

	/**
	 * @expectedException \OCA\NextNotes\Service\NotFoundException
	 */
	public function testDeleteNotFoundOne(){
		$this->service->delete(null);
	}

	/**
	 * @expectedException \OCA\NextNotes\Service\NotFoundException
	 */
	public function testDeleteNotFoundTwo(){
		$titles = ['title1', 'title2'];
		$this->tagM->expects($this->once())
			->method('delete')
			->with($this->equalTo($titles))
			->will($this->returnValue(false));
		$this->service->delete($titles);
	}

}