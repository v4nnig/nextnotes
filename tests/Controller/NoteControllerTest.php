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

use OCA\NextNotes\Controller\NoteController;
use OCA\NextNotes\Tests\TestCase;
use OCP\AppFramework\Http;
use OCA\NextNotes\Service\NotFoundException;


class NoteControllerTest extends TestCase {

    protected $controller;
    protected $service;
    protected $userId = 'john';
    protected $request;

    public function setUp() {
        $this->request = $this->getMockBuilder('OCP\IRequest')->getMock();
        $this->service = $this->getMockBuilder('OCA\NextNotes\Service\NoteService')
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller = new NoteController(
            'nextnotes', $this->request, $this->service, $this->userId
        );
    }

    public function testIndex(){
        $note = 'just check if this value is returned correctly';
        $this->service->expects($this->once())
            ->method('findAll')
            ->with($this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->controller->index();

        $this->assertEquals($note, $result->getData());
        $this->assertEquals(Http::STATUS_OK, $result->getStatus());
    }

    public function testShow(){
        $note = 'just check if this value is returned correctly';
        $this->service->expects($this->once())
            ->method('find')
            ->with($this->equalTo(1),
                $this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->controller->show(1);

        $this->assertEquals($note, $result->getData());
        $this->assertEquals(Http::STATUS_OK, $result->getStatus());
    }

    public function testShowNotFound(){
        // test the correct status code if no note is found
        $this->service->expects($this->once())
            ->method('find')
            ->will($this->throwException(new NotFoundException()));

        $result = $this->controller->show(3);

        $this->assertEquals(Http::STATUS_NOT_FOUND, $result->getStatus());
    }

    public function testCreate(){
        $note = 'just check if this value is returned correctly';
        $this->service->expects($this->once())
            ->method('create')
            ->with($this->equalTo('title'),
                $this->equalTo('content'),
                $this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->controller->create('title', 'content');

        $this->assertEquals($note, $result->getData());
        $this->assertEquals(Http::STATUS_OK, $result->getStatus());
    }

    public function testCreateNotFound(){
        // test the correct status code if note could not created
        $this->service->expects($this->once())
            ->method('create')
            ->will($this->throwException(new NotFoundException()));
        $result = $this->controller->create('','');
        $this->assertEquals(Http::STATUS_NOT_FOUND, $result->getStatus());
    }

    public function testUpdate() {
        $note = 'just check if this value is returned correctly';
        $this->service->expects($this->once())
            ->method('update')
            ->with($this->equalTo(3),
                $this->equalTo('title'),
                $this->equalTo('content'),
                $this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->controller->update(3, 'title', 'content');

        $this->assertEquals($note, $result->getData());
        $this->assertEquals(Http::STATUS_OK, $result->getStatus());
    }

    public function testUpdateNotFound() {
        // test the correct status code if no note is found
        $this->service->expects($this->once())
            ->method('update')
            ->will($this->throwException(new NotFoundException()));

        $result = $this->controller->update(3, 'title', 'content');

        $this->assertEquals(Http::STATUS_NOT_FOUND, $result->getStatus());
    }

    public function testDestroy() {
        $note = 'just check if this value is returned correctly';
        $this->service->expects($this->once())
            ->method('delete')
            ->with($this->equalTo(3),
                $this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->controller->destroy(3);

        $this->assertEquals($note, $result->getData());
        $this->assertEquals(Http::STATUS_OK, $result->getStatus());
    }

    public function testDestroyNotFound() {
        // test the correct status code if no note is found
        $this->service->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new NotFoundException()));

        $result = $this->controller->destroy(3);

        $this->assertEquals(Http::STATUS_NOT_FOUND, $result->getStatus());
    }

    public function testSearch() {
        $note = 'just check if this value is returned correctly';
        $this->service->expects($this->once())
            ->method('search')
            ->with($this->equalTo('query'),
                $this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->controller->search('query');

        $this->assertEquals($note, $result->getData());
        $this->assertEquals(Http::STATUS_OK, $result->getStatus());
    }

    public function testSearchNotFound() {
        // test the correct status code if no note is found
        $this->service->expects($this->once())
            ->method('search')
            ->will($this->throwException(new NotFoundException()));

        $result = $this->controller->search('query');

        $this->assertEquals(Http::STATUS_NOT_FOUND, $result->getStatus());
    }

}