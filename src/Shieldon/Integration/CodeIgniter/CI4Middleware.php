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

namespace Shieldon\Integration\CodeIgniter;

use Shieldon\Firewall;
use CodeIgniter\HTTP\RequestInterface as Request;
use CodeIgniter\HTTP\ResponseInterface as Response;
use CodeIgniter\Filters\FilterInterface;

/**
 * CodeIgniter 4 Middleware of Shieldon Firewall.
 */
class CI4Middleware implements FilterInterface
{
    /**
     * The absolute path of the storage where stores Shieldon generated data.
     *
     * @var string
     */
    protected $storage = '';

    /**
     * Constructor.
     *
     * @param string $storage See property `storage` explanation.
     */
    public function __construct($storage = '')
    {
        // shieldon folder is placed above wwwroot for best security, this folder must be writable.
        $this->storage = dirname($_SERVER['SCRIPT_FILENAME']) . '/../writable';

        if ('' !== $storage) {
            $this->storage = $storage;
        }
    }

	/**
     * Shieldon middleware invokable class.
     *
	 * @param Request $request
	 *
	 * @return mixed
	 */
	public function before(Request $request)
	{
		if ($request->isCLI()) {
			return;
		}

        $firewall = new Firewall($this->storage);

        // Pass CodeIgniter CSRF Token to Captcha form.
        $firewall->getShieldon()->setCaptcha(new \Shieldon\Captcha\Csrf([
			'name' => csrf_token(),
			'value' => csrf_hash(),
        ]));

        $firewall->restful();
        $firewall->run();
	}

	/**
	 * We don't have anything to do here.
	 *
	 * @param Response $request
	 * @param Response $response
	 *
	 * @return mixed
	 */
	public function after(Request $request, Response $response)
	{

    }
}