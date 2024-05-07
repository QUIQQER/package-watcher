<?php

/**
 * This file contains QUI\Watcher\EventsReact
 */

namespace QUI\Watcher;

use QUI;
use QUI\Cache\Manager as CacheManager;
use QUI\ERP\Accounting\Payments\Transactions\Factory;
use QUI\Exception;

use function date;
use function is_array;
use function is_numeric;
use function is_string;
use function json_encode;

/**
 * Class EventsReact
 *
 * @package quiqqer/watcher
 * @author  www.pcsg.de (Henning Leutz)
 * @licence For copyright and license information, please view the /README.md
 */
class EventsReact
{
    /**
     * @var array|null
     */
    protected static ?array $watcherEvents = null;

    /**
     *
     * @param string $event
     * @param array $arguments
     * @throws Exception
     */
    public static function trigger(string $event, array $arguments = []): void
    {
        // admin events
        if (
            $event == 'headerLoaded'
            || $event == 'adminLoad'
            || $event == 'adminLoadFooter'
        ) {
            return;
        }

        // users events
        if ($event == 'userLoad') {
            return;
        }

        // site events
        if (
            $event == 'siteInit'
            || $event == 'siteLoad'
            || $event == 'siteCheckActivate'
            || $event == 'siteCheckDeactivate'
        ) {
            return;
        }

        // smarty events
        if ($event == 'smartyInit') {
            return;
        }

        if (!QUI::getUserBySession()->canUseBackend()) {
            return;
        }

        $Config = QUI::getPackage('quiqqer/watcher')->getConfig();

        if (!$Config->getValue('settings', 'logEvents')) {
            return;
        }

        switch ($event) {
            case 'userLogin':
            case 'userSave':
            case 'userSetPassword':
            case 'userDisable':
            case 'userActivate':
            case 'userDeactivate':
            case 'userDelete':
            case 'projectConfigSave':
            case 'createProject':
            case 'packageSetup':
            case 'packageInstall':
            case 'packageUninstall':
            case 'siteActivate':
            case 'siteDeactivate':
            case 'siteSave':
            case 'siteDelete':
            case 'siteDestroy':
            case 'siteCreateChild':
            case 'siteMove':
            case 'mediaActivate':
            case 'mediaDeactivate':
            case 'mediaSaveBegin':
            case 'mediaSave':
            case 'mediaDelete':
            case 'mediaDeleteBegin':
            case 'mediaDestroy':
            case 'mediaRename':
                QUI\Watcher::add(
                    'quiqqer/watcher',
                    'watcher.message.' . $event,
                    $event,
                    $arguments,
                    $arguments
                );

                return;
        }


        $events = self::getWatchEvents();

        if (!isset($events['event'][$event])) {
            return;
        }

        $data = $events['event'][$event];

        foreach ($data as $entry) {
            $exec = $entry['exec'];

            if (is_callable($exec)) {
                try {
                    $str = call_user_func_array($exec, [
                        'event' => $event,
                        'params' => $arguments
                    ]);

                    QUI\Watcher::addString($str, $event, $arguments);
                } catch (\Exception $Exception) {
                    QUI\System\Log::writeException($Exception);
                }
            }
        }
    }

    /**
     * event on ajax call - React at ajax events
     *
     * @param string|array $function
     * @param string $result
     * @param array $params
     * @throws Exception
     */
    public static function onAjaxCall(string|array $function, string|array $result, array $params): void
    {
        $Config = QUI::getPackage('quiqqer/watcher')->getConfig();

        if (!$Config->getValue('settings', 'logAjax')) {
            return;
        }

        if (is_array($function)) {
            foreach ($function as $func) {
                if (is_string($func)) {
                    self::onAjaxCall($func, $result, $params);
                }
            }

            return;
        }

        $events = self::getWatchEvents();

        if (!isset($events['ajax'][$function])) {
            return;
        }

        $data = $events['ajax'][$function];

        foreach ($data as $entry) {
            $exec = $entry['exec'];

            if (is_callable($exec)) {
                try {
                    $str = call_user_func_array($exec, [
                        'ajax' => $function,
                        'params' => $params,
                        'result' => $result
                    ]);

                    QUI\Watcher::addString($str, $function, $params);
                } catch (\Exception $Exception) {
                    QUI\System\Log::writeException($Exception);
                }
            }
        }
    }

    /**
     * Register watch events
     */
    public static function onHeaderLoaded(): void
    {
        $Config = QUI::getPackage('quiqqer/watcher')->getConfig();

        if (!$Config->getValue('settings', 'logEvents')) {
            return;
        }

        $events = self::getWatchEvents();

        if (empty($events['event'])) {
            return;
        }

        $Events = QUI::getEvents();

        foreach ($events['event'] as $event => $data) {
            foreach ($data as $eventData) {
                $Events->addEvent($event, function () use ($eventData) {
                    $exec = $eventData['exec'];

                    if (!is_callable($exec)) {
                        return;
                    }

                    try {
                        $str = call_user_func_array($exec, [
                            'event' => $eventData['event'],
                            'params' => func_get_args()
                        ]);

                        QUI\Watcher::addString($str, $eventData['event']);
                    } catch (\Exception $Exception) {
                        QUI\System\Log::writeException($Exception);
                    }
                });
            }
        }
    }

    /**
     * event onUserSave
     *
     * @param QUI\Interfaces\Users\User $User
     * @throws Exception
     */
    public static function onUserSave(QUI\Interfaces\Users\User $User): void
    {
        self::trigger('userSave', [
            'uid' => $User->getUUID()
        ]);
    }

    /**
     * event onUserSetPassword
     *
     * @param QUI\Interfaces\Users\User $User
     * @throws Exception
     */
    public static function onUserSetPassword(QUI\Interfaces\Users\User $User): void
    {
        self::trigger('userSetPassword', [
            'uid' => $User->getUUID()
        ]);
    }

    /**
     * event onUserDisable
     *
     * @param QUI\Interfaces\Users\User $User
     * @throws Exception
     */
    public static function onUserDisable(QUI\Interfaces\Users\User $User): void
    {
        self::trigger('userDisable', [
            'uid' => $User->getUUID()
        ]);
    }

    /**
     * event onUserActivate
     *
     * @param QUI\Users\User $User
     * @throws Exception
     */
    public static function onUserActivate(QUI\Interfaces\Users\User $User): void
    {
        self::trigger('userActivate', [
            'uid' => $User->getUUID()
        ]);
    }

    /**
     * event onUserDeactivate
     *
     * @param QUI\Users\User $User
     * @throws Exception
     */
    public static function onUserDeactivate(QUI\Interfaces\Users\User $User): void
    {
        self::trigger('userDeactivate', [
            'uid' => $User->getUUID()
        ]);
    }

    /**
     * event onUserDelete
     *
     * @param QUI\Users\User $User
     * @throws Exception
     */
    public static function onUserDelete(QUI\Interfaces\Users\User $User): void
    {
        self::trigger('userDelete', [
            'uid' => $User->getUUID()
        ]);
    }

    /**
     * event onProjectConfigSave
     *
     * @param string $project
     * @param array $config
     * @throws Exception
     */
    public static function onProjectConfigSave(string $project, array $config): void
    {
        self::trigger('projectConfigSave', [
            'project' => $project,
            'config' => $config
        ]);
    }

    /**
     * event onCreateProject
     *
     * @param QUI\Projects\Project $Project
     * @throws Exception
     */
    public static function onCreateProject(QUI\Projects\Project $Project): void
    {
        self::trigger('createProject', [
            'project' => $Project->getName(),
            'lang' => $Project->getLang()
        ]);
    }

    /**
     * event onPackageSetup
     *
     * @param QUI\Package\Package $Package
     * @throws Exception
     */
    public static function onPackageSetup(QUI\Package\Package $Package): void
    {
        self::trigger('packageSetup', [
            'package' => $Package->getName()
        ]);
    }

    /**
     * event onPackageInstall
     *
     * @param QUI\Package\Package $Package
     * @throws Exception
     */
    public static function onPackageInstall(QUI\Package\Package $Package): void
    {
        self::trigger('packageInstall', [
            'package' => $Package->getName()
        ]);
    }

    /**
     * event onPackageUninstall
     *
     * @param string $packageName
     * @throws Exception
     */
    public static function onPackageUninstall(string $packageName): void
    {
        self::trigger('packageUninstall', [
            'package' => $packageName
        ]);
    }

    /**
     * event onSiteActivate
     *
     * @param QUI\Interfaces\Projects\Site $Site
     * @throws Exception
     */
    public static function onSiteActivate(QUI\Interfaces\Projects\Site $Site): void
    {
        self::trigger('siteActivate', [
            'id' => $Site->getId(),
            'project' => $Site->getProject()->getName(),
            'lang' => $Site->getProject()->getLang()
        ]);
    }

    /**
     * event onSiteDeactivate
     *
     * @param QUI\Projects\Site $Site
     * @throws Exception
     */
    public static function onSiteDeactivate(QUI\Interfaces\Projects\Site $Site): void
    {
        self::trigger('siteDeactivate', [
            'id' => $Site->getId(),
            'project' => $Site->getProject()->getName(),
            'lang' => $Site->getProject()->getLang()
        ]);
    }

    /**
     * event onSiteSave
     *
     * @param QUI\Projects\Site $Site
     * @throws Exception
     */
    public static function onSiteSave(QUI\Interfaces\Projects\Site $Site): void
    {
        self::trigger('siteSave', [
            'id' => $Site->getId(),
            'project' => $Site->getProject()->getName(),
            'lang' => $Site->getProject()->getLang()
        ]);
    }

    /**
     * event onSiteDelete
     *
     * @param integer $siteId
     * @param QUI\Projects\Project $Project
     * @throws Exception
     */
    public static function onSiteDelete(int $siteId, QUI\Projects\Project $Project): void
    {
        self::trigger('siteDelete', [
            'id' => $siteId,
            'project' => $Project->getName(),
            'lang' => $Project->getLang()
        ]);
    }

    /**
     * event onSiteDestroy
     *
     * @param QUI\Projects\Site $Site
     * @throws Exception
     */
    public static function onSiteDestroy(QUI\Interfaces\Projects\Site $Site): void
    {
        self::trigger('siteDestroy', [
            'id' => $Site->getId(),
            'project' => $Site->getProject()->getName(),
            'lang' => $Site->getProject()->getLang()
        ]);
    }

    /**
     * event onSiteCreateChild
     *
     * @param integer $newId
     * @param QUI\Projects\Site $Parent
     * @throws Exception
     */
    public static function onSiteCreateChild(int $newId, QUI\Projects\Site $Parent): void
    {
        self::trigger('siteCreateChild', [
            'newid' => $newId,
            'id' => $Parent->getId(),
            'project' => $Parent->getProject()->getName(),
            'lang' => $Parent->getProject()->getLang()
        ]);
    }

    /**
     * event onSiteMove
     *
     * @param QUI\Projects\Site $Site
     * @param integer $parentId
     * @throws Exception
     */
    public static function onSiteMove(QUI\Interfaces\Projects\Site $Site, int $parentId): void
    {
        self::trigger('siteMove', [
            'parentId' => $parentId,
            'id' => $Site->getId(),
            'project' => $Site->getProject()->getName(),
            'lang' => $Site->getProject()->getLang()
        ]);
    }

    /**
     * event onMediaActivate
     *
     * @param QUI\Projects\Media\Item $Item
     * @throws Exception
     */
    public static function onMediaActivate(QUI\Projects\Media\Item $Item): void
    {
        self::trigger('mediaActivate', [
            'id' => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang' => $Item->getProject()->getLang()
        ]);
    }

    /**
     * event onMediaDeactivate
     *
     * @param QUI\Projects\Media\Item $Item
     * @throws Exception
     */
    public static function onMediaDeactivate(QUI\Projects\Media\Item $Item): void
    {
        self::trigger('mediaDeactivate', [
            'id' => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang' => $Item->getProject()->getLang()
        ]);
    }

    /**
     * event onMediaSaveBegin
     *
     * @param QUI\Projects\Media\Item $Item
     * @throws Exception
     */
    public static function onMediaSaveBegin(QUI\Projects\Media\Item $Item): void
    {
        self::trigger('mediaSaveBegin', [
            'id' => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang' => $Item->getProject()->getLang()
        ]);
    }

    /**
     * event onMediaSave
     *
     * @param QUI\Projects\Media\Item $Item
     * @throws Exception
     */
    public static function onMediaSave(QUI\Projects\Media\Item $Item): void
    {
        self::trigger('mediaSave', [
            'id' => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang' => $Item->getProject()->getLang()
        ]);
    }

    /**
     * event onMediaDelete
     *
     * @param QUI\Projects\Media\Item $Item
     * @throws Exception
     */
    public static function onMediaDelete(QUI\Projects\Media\Item $Item): void
    {
        self::trigger('mediaDelete', [
            'id' => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang' => $Item->getProject()->getLang()
        ]);
    }

    /**
     * event onMediaDeleteBegin
     *
     * @param QUI\Projects\Media\Item $Item
     * @throws Exception
     */
    public static function onMediaDeleteBegin(QUI\Projects\Media\Item $Item): void
    {
        self::trigger('mediaDeleteBegin', [
            'id' => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang' => $Item->getProject()->getLang()
        ]);
    }

    /**
     * event onMediaDestroy
     *
     * @param QUI\Projects\Media\Item $Item
     * @throws Exception
     */
    public static function onMediaDestroy(QUI\Projects\Media\Item $Item): void
    {
        self::trigger('mediaDestroy', [
            'id' => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang' => $Item->getProject()->getLang()
        ]);
    }

    /**
     * event onMediaRename
     *
     * @param QUI\Projects\Media\Item $Item
     * @throws Exception
     */
    public static function onMediaRename(QUI\Projects\Media\Item $Item): void
    {
        self::trigger('mediaRename', [
            'id' => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang' => $Item->getProject()->getLang()
        ]);
    }

    /**
     * Return the global watch events -> from watch.xml's
     *
     * @return array|null
     */
    protected static function getWatchEvents(): ?array
    {
        $cacheName = 'quiqqer/watcher/events';

        try {
            return CacheManager::get($cacheName);
        } catch (\Exception) {
            // re-fetch from database
        }

        if (!self::$watcherEvents) {
            try {
                $result = QUI::getDataBase()->fetch([
                    'from' => QUI::getDBTableName('watcherEvents')
                ]);
            } catch (\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
                $result = [];
            }

            foreach ($result as $entry) {
                if (!empty($entry['ajax'])) {
                    self::$watcherEvents['ajax'][$entry['ajax']][] = $entry;
                }

                if (!empty($entry['event'])) {
                    self::$watcherEvents['event'][$entry['event']][] = $entry;
                }
            }
        }

        CacheManager::set($cacheName, self::$watcherEvents);

        return self::$watcherEvents;
    }

    public static function onQuiqqerMigrationV2(QUI\System\Console\Tools\MigrationV2 $Console): void
    {
        $Console->writeLn('- Migrate watcher');


        $result = QUI::getDataBase()->fetch([
            'from' => QUI::getDBTableName('watcher')
        ]);

        foreach ($result as $entry) {
            $uid = $entry['uid'];

            if (!is_numeric($uid)) {
                continue;
            }

            try {
                QUI::getDataBase()->update(
                    QUI::getDBTableName('watcher'),
                    ['uid' => QUI::getUsers()->get($uid)->getUUID()],
                    ['id' => $entry['id']]
                );
            } catch (QUI\Exception) {
            }
        }
    }
}
