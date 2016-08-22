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

\OCP\Share::registerBackend('nextnotes', 'OCA\NextNotes\Share\Share_Backend_Notes');

$app = new \OCA\NextNotes\AppInfo\Application();
$app->registerNavigationEntry();
$app->registerHooks();