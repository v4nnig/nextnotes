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

/**
 * Class Application
 *
 * @package OCA\NextNotes\AppInfo
 */
class Application extends App {

	/**
	 * Application constructor.
	 *
	 * @param array $urlParams
	 */
	public function __construct(array $urlParams = array()) {
		parent::__construct('nextnotes', $urlParams);
		$container = $this->getContainer();
		
	}

	/**
	 * Register the navigation entry
	 */
	public function registerNavigationEntry() {
		$container = $this->getContainer();
		$container->query('OCP\INavigationManager')->add(function () use ($container) {
			$urlGenerator = $container->query('OCP\IURLGenerator');
			$l10n = $container->query('OCP\IL10N');
			return [
				// the string under which your app will be referenced in nextcloud
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
	}
}
