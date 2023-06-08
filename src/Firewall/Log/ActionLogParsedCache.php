<?php
/**
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * php version 7.1.0
 *
 * @category  Web-security
 * @package   Shieldon
 * @author    Terry Lin <contact@terryl.in>
 * @copyright 2019 terrylinooo
 * @license   https://github.com/terrylinooo/shieldon/blob/2.x/LICENSE MIT
 * @link      https://github.com/terrylinooo/shieldon
 * @see       https://shieldon.io
 */

declare(strict_types=1);

namespace Shieldon\Firewall\Log;

use function date;
use function file_get_contents;
use function file_put_contents;
use function json_encode;
use function rtrim;
use function strtotime;
use function time;
use const JSON_PRETTY_PRINT;

/**
 * Cache the data parsed by ActionLogParser.
 */
final class ActionLogParsedCache
{
    /**
     * $directory The directory where to store the logs in.
     *
     * @var string
     */
    protected $directory = '';

    /**
     * Constructer.
     *
     * @param string $directory The directory where to store the logs in.
     */
    public function __construct(string $directory = '')
    {
        $this->directory = $directory;
    }

    /**
     * Save the data into a cache file.
     *
     * @param string $type The period type of action logs.
     * @param string $data The parsed data of action logs.
     *                     The keys are `time`, `ip_details`, `period_data`.
     *
     * @return self
     */
    public function save(string $type = 'today', array $data = []): self
    {
        $data['time'] = time();

        $filePath = rtrim($this->directory, '/') . '/cache_' . $type . '.json';

        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));

        return $this;
    }

    /**
     * Get the data from a cache file.
     *
     * @param string $type The period type of action logs.
     *
     * @return array
     */
    public function get(string $type = 'today'): array
    {
        $data = [];
        $filePath = rtrim($this->directory, '/') . '/cache_' . $type . '.json';

        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $data = json_decode($content, true);

            $cacheTime = $data['time'];

            $keepCache = true;

            switch ($type) {
                case 'yesterday':
                case 'past_seven_days':

                    // Update cache file daily.
                    $endTime = strtotime(date('Y-m-d', strtotime('-1 day')));
                    $beginTime = strtotime(date('Y-m-d', strtotime('-2 days')));
                    break;

                case 'last_month':

                    // Update cache file monthly.
                    $endTime = strtotime(date('Y-m-d', strtotime('-1 month')));
                    $beginTime = strtotime(date('Y-m-d', strtotime('-2 months')));
                    break;

                case 'this_month':

                    // Update cache file daily.
                    $endTime = strtotime(date('Y-m-d', strtotime('-1 day')));
                    $beginTime = strtotime(date('Y-m') . '-01');
                    break;

                case 'past_seven_hours':
                case 'today':
                default:
                    // Update cache file hourly.
                    $endTime = strtotime(date('Y-m-d H:00:00', time()));
                    $beginTime = strtotime(date('Y-m-d H:00:00', strtotime('-1 hour')));
                    break;
            }

            // The cacheTime is between beginTime and endTime.
            // @codeCoverageIgnoreStart
            if (($beginTime < $cacheTime) && ($endTime > $cacheTime)) {
                $keepCache = false;
            }

            if (!$keepCache) {
                $data = [];
            }
            // @codeCoverageIgnoreEnd
        }

        return $data;
    }
}
