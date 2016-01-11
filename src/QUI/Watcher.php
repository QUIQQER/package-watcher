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
    protected static $groups = null;

    /**
     * list of group ids
     *
     * @var null
     */
    protected static $users = null;

    /**
     * list of checked users
     *
     * @var array
     */
    protected static $checked = array();

    /**
     * Add a simple string to the watch-log
     *
     * @param string $message - Message
     * @param string $call - php call, eq: ajax function or event name
     * @param array $callParams - optional, call parameter
     */
    public static function addString($message = '', $call = '', $callParams = array())
    {
        if (empty($message)) {
            return;
        }

        if (!self::insertCheck()) {
            return;
        }

        QUI::getDataBase()->insert(QUI::getDBTableName('watcher'), array(
            'message' => $message,
            'call' => $call,
            'callParams' => json_encode($callParams),
            'uid' => QUI::getUserBySession()->getId(),
            'statusTime' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Add locale data to the watch-log
     *
     * @param string $localeGroup - locale group
     * @param string $localeVar - locale variable
     * @param string $call - php call, eq: ajax function or event name
     * @param array $callParams - optional, call parameter
     * @param array $localeParams - optional, locale parameter
     */
    public static function add(
        $localeGroup,
        $localeVar,
        $call = '',
        $callParams = array(),
        $localeParams = array()
    ) {
        if (!self::insertCheck()) {
            return;
        }

        QUI::getDataBase()->insert(QUI::getDBTableName('watcher'), array(
            'localeGroup' => $localeGroup,
            'localeVar' => $localeVar,
            'localeParams' => json_encode($localeParams),
            'call' => $call,
            'callParams' => json_encode($callParams),
            'uid' => QUI::getUserBySession()->getId(),
            'statusTime' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Should be logged for the group or user?
     *
     * @return bool
     */
    protected static function insertCheck()
    {
        $User = QUI::getUserBySession();
        $uid  = $User->getId();

        if (isset(self::$checked[$uid])) {
            return self::$checked[$uid];
        }


        if (!is_array(self::$groups) || !is_array(self::$users)) {
            $ugs = QUI\UsersGroups\Utils::parseUsersGroupsString(
                QUI::getPackage('quiqqer/watcher')
                    ->getConfig()
                    ->getValue('settings', 'users_and_groups')
            );

            foreach ($ugs['groups'] as $_gid) {
                self::$groups[$_gid] = true;
            }

            foreach ($ugs['users'] as $_uid) {
                self::$users[$_uid] = true;
            }
        }


        $User = QUI::getUserBySession();

        if (isset(self::$users[$User->getId()])) {
            self::$checked[$uid] = true;

            return true;
        }

        $groups = $User->getGroups();

        /* @var $Group \QUI\Groups\Group */
        foreach ($groups as $Group) {
            if (isset(self::$groups[$Group->getId()])) {
                self::$checked[$uid] = true;

                return true;
            }
        }

        self::$checked[$uid] = false;

        return false;
    }

    /**
     * Return the watcher-log list
     *
     * @param array $params - database query params (eq: order, limit)
     * @param array|bool $search - search parameter
     *
     * @return array
     */
    public static function getList($params = array(), $search = false)
    {
        $PDO = QUI::getDataBase()->getPDO();

        if (!isset($params['order'])) {
            $params['order'] = 'statusTime';
        }

        if (!isset($params['limit'])) {
            $params['limit'] = 0;
        }

        $query = 'SELECT * ';

        if (isset($params['count'])) {
            $query = 'SELECT COUNT(*) as count ';
        }

        $query .= ' FROM ' . QUI::getDBTableName('watcher');


        // search
        if (is_array($search)) {
            $searchQuery = array();

            if ($search['uid'] && !empty($search['uid'])) {
                $searchQuery[] = 'uid = :uid';
            }

            if ($search['from'] && !empty($search['from'])) {
                $searchQuery[] = 'statusTime >= :from';
            }

            if ($search['to'] && !empty($search['to'])) {
                $searchQuery[] = 'statusTime <= :to';
            }

            $query .= ' WHERE ' . implode(' AND ', $searchQuery);
        }


        // order
        switch ($params['order']) {
            case 'id':
            case 'id DESC':
            case 'id ASC':
                $query .= ' ORDER BY ' . $params['order'];
                break;

            case 'uid':
            case 'uid DESC':
            case 'statusTime':
            case 'statusTime DESC':
                $query .= ' ORDER BY ' . $params['order'] . ', id DESC';
                break;

            case 'uid ASC':
            case 'statusTime ASC':
                $query .= ' ORDER BY ' . $params['order'] . ', id ASC';
                break;

            default:
                $query .= ' ORDER BY statusTime ASC';
        }

        // limit
        if ($params['limit'] !== false) {
            $query .= ' LIMIT :limit1, :limit2';
        }


        $Statement = $PDO->prepare($query);

        // prepared statements
        if ($params['limit'] !== false) {
            if (strpos($params['limit'], ',') === false) {
                $limit1 = 0;
                $limit2 = (int)$params['limit'];
            } else {
                $params['limit'] = explode(',', $params['limit']);

                $limit1 = (int)$params['limit'][0];
                $limit2 = (int)$params['limit'][1];
            }

            $Statement->bindValue(':limit1', $limit1, \PDO::PARAM_INT);
            $Statement->bindValue(':limit2', $limit2, \PDO::PARAM_INT);
        }

        if (is_array($search)) {
            if ($search['uid'] && !empty($search['uid'])) {
                $Statement->bindValue(':uid', $search['uid'], \PDO::PARAM_STR);
            }

            if ($search['from'] && !empty($search['from'])) {
                $Statement->bindValue(
                    ':from',
                    $search['from'],
                    \PDO::PARAM_STR
                );
            }

            if ($search['to'] && !empty($search['to'])) {
                $Statement->bindValue(':to', $search['to'], \PDO::PARAM_STR);
            }
        }


        try {
            $Statement->execute();

        } catch (\PDOException $Exception) {
            QUI\System\Log::writeException($Exception);

            return array();
        }

        return $Statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Return the result list for a Grid control
     *
     * @param array $params - database query params (eq: order, limit)
     * @param array|bool $search - search parameter
     *
     * @return array
     */
    public static function getGridList($params = array(), $search = false)
    {
        $Grid     = new QUI\Utils\Grid();
        $dbParams = $Grid->parseDBParams($params);

        if (!isset($params['sortOn'])) {
            $params['sortOn'] = 'statusTime';
        }

        if (!isset($params['sortOn'])) {
            $params['sortBy'] = 'DESC';
        }

        if (isset($params['perPage']) && isset($params['page'])) {
            $params['limit']
                = (($params['page'] - 1) * $params['perPage']) . ','
                  . $params['perPage'];
        }

        $order = $params['sortOn'] . ' ' . $params['sortBy'];

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
        $result            = self::getList($dbParams, $search);

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
        $dbParams['count'] = true;
        $count             = self::getList($dbParams, $search);

        return $Grid->parseResult($result, $count[0]['count']);
    }

    /**
     * Clear the Watcher-Log
     *
     * @param string $date - date
     *
     * @throws QUI\Exception
     */
    public static function clear($date)
    {
        QUI\Rights\Permission::checkPermission('quiqqer.watcher.clearlog');

        $date = strtotime($date);

        if (!$date) {
            throw new QUI\Exception(
                QUI::getLocale()->get(
                    'quiqqer/watcher',
                    'exception.quiqqer.watcher.clearlog.error.wrongDateFormat'
                )
            );
        }


        QUI::getDataBase()->delete(QUI::getDBTableName('watcher'), array(
            'statusTime' => array(
                'type' => '<=',
                'value' => date('Y-m-d H:i:s', $date)
            )
        ));
    }

    /**
     * @param Package\Package $Package
     */
    public static function onPackageSetup(QUI\Package\Package $Package)
    {
        $dir        = $Package->getDir();
        $watcherXml = $dir . 'watch.xml';

        if (!file_exists($watcherXml)) {
            return;
        }

        $Dom  = QUI\Utils\XML::getDomFromXml($watcherXml);
        $Path = new \DOMXPath($Dom);

        $watchList = $Path->query("//quiqqer/watch");
        $table     = QUI::getDBTableName('watcherEvents');
        $package   = $Package->getName();

        // clear watches of package
        QUI::getDataBase()->delete($table, array(
            'package' => $package
        ));

        // insert watches
        foreach ($watchList as $Watch) {
            /* @var $Watch \DOMElement */
            $ajax  = $Watch->getAttribute('ajax');
            $exec  = $Watch->getAttribute('exec');
            $event = $Watch->getAttribute('event');

            if (!$exec || !is_callable($exec)) {
                continue;
            }

            if ($ajax) {
                QUI::getDataBase()->insert($table, array(
                    'package' => $package,
                    'ajax' => $ajax,
                    'exec' => $exec
                ));

                continue;
            }

            QUI::getDataBase()->insert($table, array(
                'package' => $package,
                'event' => $event,
                'exec' => $exec
            ));
        }
    }
}
