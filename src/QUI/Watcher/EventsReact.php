<?php

/**
 * This file contains QUI\Watcher\EventsReact
 */
namespace QUI\Watcher;

use QUI;

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
     *
     * @param string $event
     * @param array  $arguments
     */
    static function trigger($event, $arguments = array())
    {
        if (!is_string($event)) {
            return;
        }

        // admin events
        if ($event == 'headerLoaded'
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
        if ($event == 'siteInit'
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

        if (!QUI::getUserBySession()->isAdmin()) {
            return;
        }

        $Config = QUI::getPackage('quiqqer/watcher')->getConfig();

        // ajax event
        if ($event == 'ajaxCall') {

            if (!$Config->getValue('settings', 'logAjax')) {
                return;
            }

            // @todo ajax registrierungen
            // @todo watcher.xml


            QUI\Watcher::add(
                'quiqqer/watcher',
                'watcher.message.ajax',
                $arguments['function'],
                $arguments['params']
            );

            return;
        }

        if (!$Config->getValue('settings', 'logEvents')) {
            return;
        }

//        $var = 'watcher.message';

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
                $var = 'watcher.message.'.$event;
                break;

            default:
                // @todo watcher.xml
                return;
        }

        QUI\Watcher::add('quiqqer/watcher', $var, $event, $arguments, $arguments);
    }

    /**
     * event onUserSave
     *
     * @param QUI\Users\User $User
     */
    static function onUserSave($User)
    {
        self::trigger('userSave', $User);
    }

    /**
     * event onUserSetPassword
     *
     * @param QUI\Users\User $User
     */
    static function onUserSetPassword($User)
    {
        self::trigger('userSetPassword', array(
            'uid' => $User->getId()
        ));
    }

    /**
     * event onUserDisable
     *
     * @param QUI\Users\User $User
     */
    static function onUserDisable($User)
    {
        self::trigger('userDisable', array(
            'uid' => $User->getId()
        ));
    }

    /**
     * event onUserActivate
     *
     * @param QUI\Users\User $User
     */
    static function onUserActivate($User)
    {
        self::trigger('userActivate', array(
            'uid' => $User->getId()
        ));
    }

    /**
     * event onUserDeactivate
     *
     * @param QUI\Users\User $User
     */
    static function onUserDeactivate($User)
    {
        self::trigger('userDeactivate', array(
            'uid' => $User->getId()
        ));
    }

    /**
     * event onUserDelete
     *
     * @param QUI\Users\User $User
     */
    static function onUserDelete($User)
    {
        self::trigger('userDelete', array(
            'uid' => $User->getId()
        ));
    }

    /**
     * event onProjectConfigSave
     *
     * @param string $project
     * @param array  $config
     */
    static function onProjectConfigSave($project, $config)
    {
        self::trigger('projectConfigSave', array(
            'project' => $project,
            'config'  => $config
        ));
    }

    /**
     * event onCreateProject
     *
     * @param QUI\Projects\Project $Project
     */
    static function onCreateProject($Project)
    {
        self::trigger('createProject', array(
            'project' => $Project->getName(),
            'lang'    => $Project->getLang()
        ));
    }

    /**
     * event onPackageSetup
     *
     * @param QUI\Package\Package $Package
     */
    static function onPackageSetup($Package)
    {
        self::trigger('packageSetup', array(
            'package' => $Package->getName()
        ));
    }

    /**
     * event onPackageInstall
     *
     * @param QUI\Package\Package $Package
     */
    static function onPackageInstall($Package)
    {
        self::trigger('packageInstall', array(
            'package' => $Package->getName()
        ));
    }

    /**
     * event onPackageUninstall
     *
     * @param string $packageName
     */
    static function onPackageUninstall($packageName)
    {
        self::trigger('packageUninstall', array(
            'package' => $packageName
        ));
    }

    /**
     * event onSiteActivate
     *
     * @param QUI\Projects\Site $Site
     */
    static function onSiteActivate($Site)
    {
        self::trigger('siteActivate', array(
            'id'      => $Site->getId(),
            'project' => $Site->getProject()->getName(),
            'lang'    => $Site->getProject()->getLang()
        ));
    }

    /**
     * event onSiteDeactivate
     *
     * @param QUI\Projects\Site $Site
     */
    static function onSiteDeactivate($Site)
    {
        self::trigger('siteDeactivate', array(
            'id'      => $Site->getId(),
            'project' => $Site->getProject()->getName(),
            'lang'    => $Site->getProject()->getLang()
        ));
    }

    /**
     * event onSiteSave
     *
     * @param QUI\Projects\Site $Site
     */
    static function onSiteSave($Site)
    {
        self::trigger('siteSave', array(
            'id'      => $Site->getId(),
            'project' => $Site->getProject()->getName(),
            'lang'    => $Site->getProject()->getLang()
        ));
    }

    /**
     * event onSiteDelete
     *
     * @param integer              $siteId
     * @param QUI\Projects\Project $Project
     */
    static function onSiteDelete($siteId, $Project)
    {
        self::trigger('siteDelete', array(
            'id'      => $siteId,
            'project' => $Project->getName(),
            'lang'    => $Project->getLang()
        ));
    }

    /**
     * event onSiteDestroy
     *
     * @param QUI\Projects\Site $Site
     */
    static function onSiteDestroy($Site)
    {
        self::trigger('siteDestroy', array(
            'id'      => $Site->getId(),
            'project' => $Site->getProject()->getName(),
            'lang'    => $Site->getProject()->getLang()
        ));
    }

    /**
     * event onSiteCreateChild
     *
     * @param integer           $newId
     * @param QUI\Projects\Site $Parent
     */
    static function onSiteCreateChild($newId, $Parent)
    {
        self::trigger('siteCreateChild', array(
            'newid'   => $newId,
            'id'      => $Parent->getId(),
            'project' => $Parent->getProject()->getName(),
            'lang'    => $Parent->getProject()->getLang()
        ));
    }

    /**
     * event onSiteMove
     *
     * @param QUI\Projects\Site $Site
     * @param integer           $parentId
     */
    static function onSiteMove($Site, $parentId)
    {
        self::trigger('siteMove', array(
            'parentId' => $parentId,
            'id'       => $Site->getId(),
            'project'  => $Site->getProject()->getName(),
            'lang'     => $Site->getProject()->getLang()
        ));
    }

    /**
     * event onMediaActivate
     *
     * @param QUI\Projects\Media\Item $Item
     */
    static function onMediaActivate($Item)
    {
        self::trigger('mediaActivate', array(
            'id'      => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang'    => $Item->getProject()->getLang()
        ));
    }

    /**
     * event onMediaDeactivate
     *
     * @param QUI\Projects\Media\Item $Item
     */
    static function onMediaDeactivate($Item)
    {
        self::trigger('mediaDeactivate', array(
            'id'      => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang'    => $Item->getProject()->getLang()
        ));
    }

    /**
     * event onMediaSaveBegin
     *
     * @param QUI\Projects\Media\Item $Item
     */
    static function onMediaSaveBegin($Item)
    {
        self::trigger('mediaSaveBegin', array(
            'id'      => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang'    => $Item->getProject()->getLang()
        ));
    }

    /**
     * event onMediaSave
     *
     * @param QUI\Projects\Media\Item $Item
     */
    static function onMediaSave($Item)
    {
        self::trigger('mediaSave', array(
            'id'      => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang'    => $Item->getProject()->getLang()
        ));
    }

    /**
     * event onMediaDelete
     *
     * @param QUI\Projects\Media\Item $Item
     */
    static function onMediaDelete($Item)
    {
        self::trigger('mediaDelete', array(
            'id'      => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang'    => $Item->getProject()->getLang()
        ));
    }

    /**
     * event onMediaDeleteBegin
     *
     * @param QUI\Projects\Media\Item $Item
     */
    static function onMediaDeleteBegin($Item)
    {
        self::trigger('mediaDeleteBegin', array(
            'id'      => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang'    => $Item->getProject()->getLang()
        ));
    }

    /**
     * event onMediaDestroy
     *
     * @param QUI\Projects\Media\Item $Item
     */
    static function onMediaDestroy($Item)
    {
        self::trigger('mediaDestroy', array(
            'id'      => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang'    => $Item->getProject()->getLang()
        ));
    }

    /**
     * event onMediaRename
     *
     * @param QUI\Projects\Media\Item $Item
     */
    static function onMediaRename($Item)
    {
        self::trigger('mediaRename', array(
            'id'      => $Item->getId(),
            'project' => $Item->getProject()->getName(),
            'lang'    => $Item->getProject()->getLang()
        ));
    }
}
