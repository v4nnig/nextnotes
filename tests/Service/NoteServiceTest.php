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

use OCA\NextNotes\Service\NoteService;
use OCA\NextNotes\Tests\TestCase;
use OCP\AppFramework\Db\DoesNotExistException;
use OCA\NextNotes\Db\Note;

class NoteServiceTest extends TestCase {

    private $service;
    private $tagService;
    private $mapper;
    private $logger;
    private $userId = 'john';

    public function setUp() {
        $this->logger = $this->getMockBuilder('OCP\ILogger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->mapper = $this->getMockBuilder('OCA\NextNotes\Db\NoteMapper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->tagService = $this->getMockBuilder('OCA\NextNotes\Service\TagService')
            ->disableOriginalConstructor()
            ->getMock();
        $this->service = new NoteService($this->mapper,$this->tagService,$this->logger);
    }

    public function testUpdate() {
        // the existing note
        $note = Note::fromRow([
            'id' => 3,
            'title' => 'yo',
            'content' => 'nope'
        ]);
        $this->mapper->expects($this->once())
            ->method('find')
            ->with($this->equalTo(3))
            ->will($this->returnValue($note));

        // the note when updated
        $updatedNote = Note::fromRow(['id' => 3]);
        $updatedNote->setTitle('title');
        $updatedNote->setContent('content');
        $this->mapper->expects($this->once())
            ->method('update')
            ->with($this->equalTo($updatedNote))
            ->will($this->returnValue($updatedNote));

        $result = $this->service->update(3, 'title', 'content', $this->userId);

        $this->assertEquals($updatedNote, $result);
    }


    /**
     * @expectedException OCA\NextNotes\Service\NotFoundException
     */
    public function testUpdateNotFound() {
        // test the correct status code if no note is found
        $this->mapper->expects($this->once())
            ->method('find')
            ->with($this->equalTo(3))
            ->will($this->throwException(new DoesNotExistException('')));

        $this->service->update(3, 'title', 'content', $this->userId);
    }

}