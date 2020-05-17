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
 * ComponentPrivider
 */
abstract class ComponentProvider implements ComponentInterface
{
    /**
     * Data pool for Blacklist.
     *
     * @var array
     */
    protected $deniedList = [];

    /**
     * Data pool for hard whitelist.
     *
     * @var array
     */
    protected $allowedList = [];

    /**
     * It is really strict.
     *
     * @var bool
     */
    protected $strictMode = false;

    /**
     * @var \Shieldon\Request
     */
    protected $request;

    /**
     * Constroctor.
     */
    public function __construct()
    {
        $this->request = Container::get('request');
    }

    /**
     * Set denied item list. 
     *
     * @param array $stringList String list.
     *
     * @return void
     */
    public function setDeniedList(array $stringList): void
    {
        $this->deniedList = $stringList;
    }

    /**
     * Set denied item.
     *
     * @param string $string
     *
     * @return void
     */
    public function setDeniedItem(string $string): void
    {
        if (! in_array($string, $this->deniedList)) {
            array_push($this->deniedList, $string);
        }
    }

    /**
     * Return current denied list.
     *
     * @return array
     */
    public function getDeniedList(): array
    {
        return $this->deniedList;
    }

    /**
     * Enable strict mode.
     * 
     * @param bool $bool Set true to enble strict mode, false to disable it overwise.
     *
     * @return void
     */
    public function setStrict(bool $bool): void
    {
        $this->strictMode = $bool;
    }

    /**
     * Remove item.
     *
     * @param string $string
     *
     * @return void
     */
    public function removeItem(string $string): void
    {
        if (! empty($this->allowedList)) {
            $key = array_search($string, $this->allowedList);
            if (false !==  $key) {
                unset($this->allowedList[$key]);
            }
        }

        if (! empty($this->deniedList)) {
            $key = array_search($string, $this->deniedList);
            if (false !==  $key) {
                unset($this->deniedList[$key]);
            }
        }
    }

    /**
     * Is denied?
     *
     * @return bool
     */
    abstract function isDenied(): bool;

    /**
     * Unique deny status code.
     *
     * @return int
     */
    abstract function getDenyStatusCode(): int;
}