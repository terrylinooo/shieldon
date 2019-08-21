<?php declare(strict_types=1);
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 */

class Csrf
{
    /**
	 * Random hash.
	 *
	 * @var string
	 */
	protected $hash	= '';

	/**
	 * Token name.
	 *
	 * @var string
	 */
	protected $name = 'shieldon_csrf_token';

    /**
     * Constructor.
     *
     * @return	void
     */
    public function __construct()
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		// Set the CSRF hash.
		$this->setHash();
    }

	/**
	 * Verify Cross Site Request Forgery Protection
	 *
	 * @return bool
	 */
	public function verify()
	{
		// If it's not a POST request we will reset the hash.
		if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
			$this->setHash();

			// Becuase current request mothod is not POST, we return true to 
			// let the vistor continue browsing.
			return true;
		}

		// Let's start checking process.

		// Do the tokens exist in both the _POST and _SESSION?
		if (! isset($_POST[$this->name], $_SESSION[$this->name])) {
			return false;
		}

		// Do the tokens match?
		if ($_POST[$this->name] !== $_SESSION[$this->name]) {
			return false;
		}

		unset($_POST[$this->name], $_SESSION[$this->name]);

		$this->setHash();

		return true;
	}

	/**
	 * Get CSRF Hash
	 *
	 * @return string
	 */
	public function getHash(): string
	{
		return $this->hash;
	}

	/**
	 * Get CSRF Token Name
	 *
	 * @return 	string
	 */
	public function getTokenName(): string
	{
		return $this->name;
    }
    
	/**
	 * Set the hash.
	 *
	 * @return	string
	 */
	protected function setHash(): string
	{
		$this->hash = md5(uniqid(rand(), true));
		$_SESSION[$this->name] = $this->hash;

		return $this->hash;
	}
}