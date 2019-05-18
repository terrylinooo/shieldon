<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Component;

/**
 * RobotInterface
 */
interface RobotInterface
{
    /**
     * Check if a robot is in backlist.
     *
     * @return boolean
     */
    public function isDenied(): bool;

    /**
     * Check if a robot is in whitelist.
     *
     * @return boolean
     */
    public function isAllowed(): bool;

    /**
     * Check if is a robot.
     *
     * @return boolean
     */
    public function isRobot(): bool;

    /**
     * Check if is a robot from Google.
     *
     * @return boolean
     */
    public function isGoogle(): bool;

    /**
     * Check if is a robot from Bing.
     *
     * @return boolean
     */
    public function isBing(): bool;

    /**
     * Check if is a robot from Yahoo.
     *
     * @return boolean
     */
    public function isYahoo(): bool;

    /**
     * Check if is a robot from search engines.
     *
     * @return boolean
     */
    public function isSearchEngine(): bool;

    /**
     * Check if is a robot from social networking service.
     *
     * @return boolean
     */
    public function isSocialNetwork(): bool;
 }