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

namespace OCA\NextNotes\Hooks;

use OCA\NextNotes\AppInfo\Application;
use OCA\NextNotes\Service\NoteService;

/**
 * Class UserHooks
 *
 * @package OCA\NextNotes\Hooks
 */
class UserHooks {

    /**
     * postCreate User Hook
     * @param $params
     */
    public static function createUser($params) {
        self::createIntroForUser($params['uid']);
    }

    /**
     * postDelete User Hook
     * @param $params
     */
    public static function deleteUser($params) {
        self::deleteNotesForUser($params['uid']);
    }

    /**
     * Get the NoteService and delete all Notes for the user.
     * @param $userId
     */
    protected static function deleteNotesForUser($userId){
        // Delete note entries
        $app = new Application();
        /** @var NoteService */
        $noteService = $app->getContainer()->query('NoteService');
        $noteService->deleteNotesForUser($userId);
    }

    /**
     * Get the NoteService and create the first Note (Intro Note)
     * @param $userId
     */
    protected static function createIntroForUser($userId){
        // Create first note
        $app = new Application();
        /** @var NoteService */
        $noteService = $app->getContainer()->query('NoteService');
        $noteService->createIntroNoteForUser($userId);
    }
}