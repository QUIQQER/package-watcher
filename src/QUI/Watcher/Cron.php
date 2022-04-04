<?php

namespace QUI\Watcher;

use QUI\Watcher;

/**
 * Class Cron
 *
 * Cronjob handler for quiqqer/watcher
 */
class Cron
{
    /**
     * Delete all watcher entries older than X days
     *
     * @param array $params
     * @throws \QUI\Exception
     */
    public static function clearWatcherEntries($params)
    {
        if (empty($params['days'])) {
            $params['days'] = 3;
        }

        $DeleteOlderThanDate = date_create('-'.$params['days'].' day');
        Watcher::clear($DeleteOlderThanDate->format('Y-m-d'));
    }
}
