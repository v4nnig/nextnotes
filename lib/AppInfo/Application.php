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

use OCA\NextNotes\Controller\NoteApiController;
use OCA\NextNotes\Controller\NoteController;
use OCA\NextNotes\Controller\TagApiController;
use OCA\NextNotes\Controller\TagController;
use OCA\NextNotes\Db\NoteMapper;
use OCA\NextNotes\Service\NoteService;
use OCA\NextNotes\Service\TagService;
use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;
use OCP\IContainer;
use OCP\Util;

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

        /**
         * Register the Next Notes Services
         */
        $container->registerService('NoteMapper', function(IContainer $c) {
            /** @var \OC\Server $server */
            $server = $c->query('ServerContainer');
            return new NoteMapper(
                $server->getDatabaseConnection()
            );
        });

        $container->registerService('Tagger', function(IAppContainer $c) {
            /** @var \OC\Server $server */
            $server = $c->query('ServerContainer');
            return $server->getTagManager()->load($c->getAppName(), null, true, $c->query('CurrentUID'));
        });

        $container->registerService('TagService', function(IAppContainer $c) {
            /** @var \OC\Server $server */
            $server = $c->query('ServerContainer');
            return new TagService(
                $c->query('Tagger'),
				$c->query('NoteMapper'),
				$c->query('CurrentUID'),
                $server->getLogger()
            );
        });

        $container->registerService('NoteService', function(IContainer $c) {
            /** @var \OC\Server $server */
            $server = $c->query('ServerContainer');
            return new NoteService(
                $c->query('NoteMapper'),
                $c->query('TagService'),
                $server->getLogger()
            );
        });

        /**
         * Register core services
         */
        $container->registerService('CurrentUID', function(IContainer $c) {
            /** @var \OC\Server $server */
            $server = $c->query('ServerContainer');
            $user = $server->getUserSession()->getUser();
            return ($user) ? $user->getUID() : '';
        });

        /**
         * Controller
         */
        $container->registerService('NoteController', function(IAppContainer $c) {
            /** @var \OC\Server $server */
            $server = $c->query('ServerContainer');
            return new NoteController(
                $c->getAppName(),
                $server->getRequest(),
                $c->query('NoteService'),
                $c->query('CurrentUID')
            );
        });

		$container->registerService('NoteApiController', function(IAppContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');
			return new NoteApiController(
				$c->getAppName(),
				$server->getRequest(),
				$c->query('NoteService'),
				$c->query('CurrentUID')
			);
		});

        $container->registerService('TagController', function(IAppContainer $c) {
            /** @var \OC\Server $server */
            $server = $c->query('ServerContainer');
            return new TagController(
                $c->getAppName(),
                $server->getRequest(),
                $c->query('TagService'),
                $c->query('CurrentUID')
            );
        });

		$container->registerService('TagApiController', function(IAppContainer $c) {
			/** @var \OC\Server $server */
			$server = $c->query('ServerContainer');
			return new TagApiController(
				$c->getAppName(),
				$server->getRequest(),
				$c->query('TagService'),
				$c->query('CurrentUID')
			);
		});
    }

    /**
     * Register the navigation entry
     */
    public function registerNavigationEntry() {
        $container = $this->getContainer();
        $container->query('OCP\INavigationManager')->add(function() use ($container) {
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
	
    /**
     * Register Hooks
     */
    public function registerHooks() {
        // Tags
        Util::connectHook('OC_User', 'post_deleteUser', 'OC\Tags', 'post_deleteUser');
        // Notes
        Util::connectHook('OC_User', 'post_deleteUser', 'OCA\NextNotes\Hooks\UserHooks', 'deleteUser'); //DELETE
        Util::connectHook('OC_User', 'post_createUser', 'OCA\NextNotes\Hooks\UserHooks', 'createUser'); //CREATE
    }
}
