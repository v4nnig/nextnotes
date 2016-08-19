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

    public function testFindAll(){
        $note = Note::fromRow([
            'id' => 3,
            'title' => 'yo',
            'content' => 'nope'
        ]);

        $this->mapper->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->service->findAll($this->userId);

        $this->assertEquals($note, $result);
    }

    /**
     * @expectedException \OCA\NextNotes\Service\NotFoundException
     */
    public function testFindAllNotFound() {
        // test the correct status code if no note is found
        $this->mapper->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($this->userId))
            ->will($this->throwException(new DoesNotExistException('')));

        $this->service->findAll($this->userId);
    }

    public function testFind(){
        $note = Note::fromRow([
            'id' => 3,
            'title' => 'yo',
            'content' => 'nope'
        ]);

        $this->mapper->expects($this->once())
            ->method('find')
            ->with($this->equalTo(3),
                $this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->service->find(3, $this->userId);

        $this->assertEquals($note, $result);
    }

    /**
     * @expectedException \OCA\NextNotes\Service\NotFoundException
     */
    public function testFindNotFound() {
        // test the correct status code if no note is found
        $this->mapper->expects($this->once())
            ->method('find')
            ->with($this->equalTo(3),
                $this->equalTo($this->userId))
            ->will($this->throwException(new DoesNotExistException('')));

        $this->service->find(3, $this->userId);
    }

    public function testCreate(){
        $note = Note::fromRow([
            'id' => 3,
            'title' => 'yo',
            'content' => 'nope'
        ]);

        $insert = new Note();
        $insert->setTitle('yo');
        $insert->setContent('nope');
        $insert->setUserId($this->userId);

        $this->mapper->expects($this->once())
            ->method('insert')
            ->with($this->equalTo($insert))
            ->will($this->returnValue($note));

        $result = $this->service->create('yo', 'nope', $this->userId);

        $this->assertEquals($note, $result);
    }

    /**
     * @expectedException \OCA\NextNotes\Service\NotFoundException
     */
    public function testCreateNotFound() {
        // test the correct status code if no note could be created
        $insert = new Note();
        $insert->setTitle('yo');
        $insert->setContent('nope');
        $insert->setUserId($this->userId);

        $this->mapper->expects($this->once())
            ->method('insert')
            ->with($this->equalTo($insert))
            ->will($this->throwException(new DoesNotExistException('')));

        $this->service->create('yo', 'nope', $this->userId);
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
     * @expectedException \OCA\NextNotes\Service\NotFoundException
     */
    public function testUpdateNotFound() {
        // test the correct status code if no note is found
        $this->mapper->expects($this->once())
            ->method('find')
            ->with($this->equalTo(3))
            ->will($this->throwException(new DoesNotExistException('')));

        $this->service->update(3, 'title', 'content', $this->userId);
    }

    public function testDelete() {
        // the existing note
        $note = Note::fromRow([
            'id' => 3,
            'title' => 'yo',
            'content' => 'nope'
        ]);

        $this->tagService->expects($this->once())
            ->method('purgeObject')
            ->with($this->equalTo(3))
            ->will($this->returnValue(true));

        $this->mapper->expects($this->once())
            ->method('find')
            ->with($this->equalTo(3),
                $this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $this->mapper->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($note))
            ->will($this->returnValue(true));

        $result = $this->service->delete(3, $this->userId);

        $this->assertEquals($note, $result);
    }

    /**
     * @expectedException \OCA\NextNotes\Service\NotFoundException
     */
    public function testDeleteNotFound() {
        $this->tagService->expects($this->once())
            ->method('purgeObject')
            ->with($this->equalTo(3))
            ->will($this->throwException(new DoesNotExistException('')));

        $this->service->delete(3, $this->userId);
    }

    public function testSearchFulltextWithoutTagFilterOne(){
        $note = Note::fromRow([
            'id' => 3,
            'title' => 'yo',
            'content' => 'nope'
        ]);

        $query = 'Text';
        $terms = ['Text'];

        $this->mapper->expects($this->once())
            ->method('fulltextSearchWithoutTagFilter')
            ->with($this->equalTo($terms),
                $this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->service->search($query, $this->userId);
        $this->assertEquals($note, $result);
    }

    public function testSearchFulltextWithoutTagFilterTwo(){
        $note = Note::fromRow([
            'id' => 3,
            'title' => 'yo',
            'content' => 'nope'
        ]);

        $query = 'Text text';
        $terms = ['Text', 'text'];

        $this->mapper->expects($this->once())
            ->method('fulltextSearchWithoutTagFilter')
            ->with($this->equalTo($terms),
                $this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->service->search($query, $this->userId);
        $this->assertEquals($note, $result);
    }

    public function testSearchTagSearchOne(){
        $note = Note::fromRow([
            'id' => 3,
            'title' => 'yo',
            'content' => 'nope'
        ]);

        $query = '#Text#';
        $terms = ['Text'];

        $this->mapper->expects($this->once())
            ->method('tagSearch')
            ->with($this->equalTo($terms),
                $this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->service->search($query, $this->userId);
        $this->assertEquals($note, $result);
    }

    public function testSearchTagSearchTwo(){
        $note = Note::fromRow([
            'id' => 3,
            'title' => 'yo',
            'content' => 'nope'
        ]);

        $query = '#Text##nope#';
        $terms = ['Text', 'nope'];

        $this->mapper->expects($this->once())
            ->method('tagSearch')
            ->with($this->equalTo($terms),
                $this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->service->search($query, $this->userId);
        $this->assertEquals($note, $result);
    }

    public function testSearchFulltextWithTagFilter(){
        $note = Note::fromRow([
            'id' => 3,
            'title' => 'yo',
            'content' => 'nope'
        ]);

        $query = '#Text#nope';
        $tags = ['Text'];
        $terms = array('nope');

        $this->mapper->expects($this->once())
            ->method('fulltextSearchWithTagFilter')
            ->with($this->equalTo($terms),
                $this->equalTo($tags),
                $this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->service->search($query, $this->userId);
        $this->assertEquals($note, $result);
    }

    /**
     * @expectedException \OCA\NextNotes\Service\NotFoundException
     */
    public function testSearchNotFound(){
        $query = '';
        $this->service->search($query, $this->userId);
    }

    public function testDeleteNotesForUser(){
        $this->mapper->expects($this->once())
            ->method('deleteAllForUser')
            ->with($this->equalTo($this->userId))
            ->will($this->returnValue(true));
        $this->assertNull($this->service->deleteNotesForUser($this->userId));
    }

    /**
     * @expectedException \OCA\NextNotes\Service\NotFoundException
     */
    public function testDeleteNotesForUserNotFound(){
        $this->mapper->expects($this->once())
            ->method('deleteAllForUser')
            ->with($this->equalTo($this->userId))
            ->will($this->throwException(new DoesNotExistException('')));
        $this->service->deleteNotesForUser($this->userId);
    }

    public function testCreateIntroNoteForUser(){
        $note = new Note();
        $note->setTitle('# Welcome to Next Notes!');
        $note->setContent('# Welcome to Next Notes!');
        $note->setUserId($this->userId);
        $this->mapper->expects($this->once())
            ->method('insert')
            ->with($this->equalTo($note))
            ->will($this->returnValue(true));
        $this->assertNull($this->service->createIntroNoteForUser($this->userId));
    }

    /**
     * @expectedException \OCA\NextNotes\Service\NotFoundException
     */
    public function testCreateIntroNoteForUserNotFound(){
        $this->mapper->expects($this->once())
            ->method('insert')
            ->will($this->throwException(new DoesNotExistException('')));
        $this->service->createIntroNoteForUser($this->userId);
    }
}