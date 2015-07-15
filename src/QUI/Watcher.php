<?php

/**
 * This file contains QUI\Watcher
 */
namespace QUI;

use QUI;

/**
 * Class Watcher
 *
 * @package quiqqer/watcher
 * @author  www.pcsg.de (Henning Leutz)
 * @licence For copyright and license information, please view the /README.md
 */
class Watcher
{
    /**
     * list of group ids
     *
     * @var null
     */
    protected static $_groups = null;

    /**
     * list of group ids
     *
     * @var null
     */
    protected static $_users = null;

    /**
     * list of checked users
     *
     * @var array
     */
    protected static $_checked = array();


    /**
     * Add a simple string to the watch-log
     *
     * @param string $message    - Message
     * @param string $call       - php call, eq: ajax function or event name
     * @param array  $callParams - optional, call parameter
     */
    static function addString($message = '', $call = '', $callParams = array())
    {
        if (empty($message)) {
            return;
        }

        if (!self::_insertCheck()) {
            return;
        }

        QUI::getDataBase()->insert(QUI::getDBTableName('watcher'), array(
            'text'       => $message,
            'call'       => $call,
            'callParams' => json_encode($callParams),
            'uid'        => QUI::getUserBySession()->getId(),
            'statusTime' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Add locale data to the watch-log
     *
     * @param string $localeGroup  - locale group
     * @param string $localeVar    - locale variable
     * @param string $call         - php call, eq: ajax function or event name
     * @param array  $callParams   - optional, call parameter
     * @param array  $localeParams - optional, locale parameter
     */
    static function add(
        $localeGroup,
        $localeVar,
        $call = '',
        $callParams = array(),
        $localeParams = array()
    ) {
        if (!self::_insertCheck()) {
            return;
        }

        QUI::getDataBase()->insert(QUI::getDBTableName('watcher'), array(
            'localeGroup'  => $localeGroup,
            'localeVar'    => $localeVar,
            'localeParams' => json_encode($localeParams),
            'call'         => $call,
            'callParams'   => json_encode($callParams),
            'uid'          => QUI::getUserBySession()->getId(),
            'statusTime'   => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Should be logged for the group or user?
     *
     * @return bool
     */
    protected static function _insertCheck()
    {
        $User = QUI::getUserBySession();
        $uid = $User->getId();

        if (isset(self::$_checked[$uid])) {
            return self::$_checked[$uid];
        }


        if (!is_array(self::$_groups) || !is_array(self::$_users)) {

            $ugs = QUI\UsersGroups\Utils::parseUsersGroupsString(
                QUI::getPackage('quiqqer/watcher')
                   ->getConfig()
                   ->getValue('settings', 'users_and_groups')
            );

            foreach ($ugs['groups'] as $_gid) {
                self::$_groups[$_gid] = true;
            }

            foreach ($ugs['users'] as $_uid) {
                self::$_users[$_uid] = true;
            }
        }


        $User = QUI::getUserBySession();

        if (isset(self::$_users[$User->getId()])) {
            self::$_checked[$uid] = true;

            return true;
        }

        $groups = $User->getGroups();

        /* @var $Group \QUI\Groups\Group */
        foreach ($groups as $Group) {
            if (isset(self::$_groups[$Group->getId()])) {
                self::$_checked[$uid] = true;

                return true;
            }
        }

        self::$_checked[$uid] = false;

        return false;
    }

    /**
     * Return the watcher-log list
     *
     * @param array $params - database query params (eq: order, limit)
     *
     * @return array
     */
    static function getList($params = array())
    {
        if (!isset($params['order'])) {
            $params['order'] = 'statusTime';
        }

        if (!isset($params['limit'])) {
            $params['limit'] = 0;
        }


        switch ($params['order']) {
            case 'id':
            case 'id DESC':
            case 'id ASC':
                break;

            case 'uid':
            case 'uid DESC':
            case 'statusTime':
            case 'statusTime DESC':
                $params['order'] = $params['order'].', id DESC';
                break;

            case 'uid ASC':
            case 'statusTime ASC':
                $params['order'] = $params['order'].', id ASC';
                break;

            default:
                $params['order'] = 'statusTime ASC';
        }

        $params['from'] = QUI::getDBTableName('watcher');

        return QUI::getDataBase()->fetch($params);
    }

    /**
     * Return the result list for a Grid control
     *
     * @param array $params
     *
     * @return array
     */
    static function getGridList($params = array())
    {
        $Grid = new QUI\Utils\Grid();
        $dbParams = $Grid->parseDBParams($params);

        if (!isset($params['sortOn'])) {
            $params['sortOn'] = 'statusTime';
        }

        if (!isset($params['sortOn'])) {
            $params['sortBy'] = 'DESC';
        }

        $order = $params['sortOn'].' '.$params['sortBy'];

        switch ($order) {
            case 'id':
            case 'id DESC':
            case 'id ASC':
            case 'statusTime':
            case 'statusTime DESC':
            case 'statusTime ASC':
                break;

            default:
                $order = 'statusTime DESC';
        }


        $dbParams['order'] = $order;
        $result = self::getList($dbParams);

        foreach ($result as $key => $value) {

            if (!empty($value['localeGroup']) && !empty($value['localeVar'])) {

                $localeParams = json_decode($value['localeParams'], true);

                $result[$key]['message'] = QUI::getLocale()->get(
                    $value['localeGroup'],
                    $value['localeVar'],
                    $localeParams
                );
            }

            try {
                $result[$key]['username'] = QUI::getUsers()
                                               ->get($value['uid'])
                                               ->getUsername();

            } catch (QUI\Exception $Exception) {

                $result[$key]['username'] = 'unknown';

            }
        }

        $dbParams['limit'] = false;
        $dbParams['count'] = 'count';
        $count = self::getList($dbParams);

        return $Grid->parseResult($result, $count[0]['count']);
    }


    /**
     *
     * @param $date
     */
    static function clean($date)
    {

    }
}
