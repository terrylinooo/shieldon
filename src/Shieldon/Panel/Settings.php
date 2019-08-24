<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Panel;

/**
 * Settings.
 *
 */
final class Settings extends Configure
{
    /**
     * Constructor.
     *
     * @param string $configFile
     */
    public function __construct($configFile)
    {
        parent::__construct($configFile);
        
        $this->formHandler();
    }
    
    /**
     * Form handler.
     *
     * @return void
     */
    protected function formHandler()
    {

    }
}