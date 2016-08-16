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

namespace OCA\NextNotes\AppInfo;


use OCP\AppFramework\App;
use OCP\Util;

require_once __DIR__ . '/autoload.php';

$app = new App('nextnotes');
$container = $app->getContainer();

/**
 * Create the Navigation in Nextcloud
 */
$container->query('OCP\INavigationManager')->add(function () use ($container) {
	$urlGenerator = $container->query('OCP\IURLGenerator');
	$l10n = $container->query('OCP\IL10N');
	return [
		// the string under which your app will be referenced in owncloud
		'id' => 'nextnotes',

		// sorting weight for the navigation. The higher the number, the higher
		// will it be listed in the navigation
		'order' => 10,

		// the route that will be shown on startup
		'href' => $urlGenerator->linkToRoute('nextnotes.page.index'),

		// the icon that will be shown in the navigation
		// this file needs to exist in img/
		'icon' => $urlGenerator->imagePath('nextnotes', 'app.svg'),

		// the title of your application. This will be used in the
		// navigation or on the settings page of your app
		'name' => $l10n->t('Next Notes'),
	];
});

/**
 * FIXME: Register the Hooks for the User (NoteService)
 * TODO: If Tag post delete User hook is registered in the core, this i not necessary anymore. OR own implementation of tags because of the share thing.
 */
Util::connectHook('OC_User', 'post_deleteUser', 'OC\Tags', 'post_deleteUser');
