<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon Firewall package, an enhanced package for Shieldon library.
 * It's lincese is exactly defferent to Shieldon package, pelase read the license 
 * information below.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * @author     Terry L. <contact@terryl.in>
 * @package    Shieldon
 * @subpackage Shieldon Firewall
 * @link       https://shieldon.io
 * @license    Free to use when reserving the credit link, see explanation below.
 * 
 *                                  *** License ***
 *
 * Shieldon Firewall is free for both personal and commercial use If the Shieldon's credit link 
 * is displayed on every Shieldon-generated pages such as CAPTCHA page、password protection page 
 * and so on. If you are willing to remove the credit link, please purchase a commercail license 
 * from https://shieldon.io to support use make it better.
 */

namespace Shieldon\Firewall;

/**
 * IP Manager.
 */
final class IpManager extends Configure
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