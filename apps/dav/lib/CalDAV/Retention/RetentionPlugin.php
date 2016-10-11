<?php
/**
 * @copyright Copyright (c) 2016, Georg Ehrke
 *
 * @author Georg Ehrke <georg@nextcloud.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OCA\DAV\CalDAV\Retention;

use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;
use Sabre\DAV\Exception\NotFound;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

class RetentionPlugin extends ServerPlugin {

	/**
	 * Reference to SabreDAV server object.
	 *
	 * @var \Sabre\DAV\Server
	 */
	protected $server;

	/**
	 * initialize plugin
	 * @param Server $server
	 */
	public function initialize(Server $server) {
		$this->server = $server;

		$this->server->on('beforeMethod:PROPFIND',	[$this, 'beforeMethod']);
		$this->server->on('beforeMethod:REPORT',	[$this, 'beforeMethod']);

		$this->server->on('method:POST', [$this, 'httpPost']);
	}

	/**
	 * This method should return a list of server-features.
	 *
	 * This is for example 'versioning' and is added to the DAV: header
	 * in an OPTIONS response.
	 *
	 * @return string[]
	 */
	public function getFeatures() {
		return ['nc-calendar-retention'];
	}

	/**
	 * Returns a plugin name.
	 *
	 * Using this name other plugins will be able to access other plugins
	 * using Sabre\DAV\Server::getPlugin
	 *
	 * @return string
	 */
	public function getPluginName()	{
		return 'nc-calendar-retention';
	}

	/**
	 * @param RequestInterface $request
	 */
	public function beforeMethod(RequestInterface $request) {
		$path = $request->getPath();
		$method = $request->getMethod();

		// Making sure the node exists
		try {
			$node = $this->server->tree->getNodeForPath($path);
		} catch (NotFound $e) {
			return;
		}

		if (($method === 'PROPFIND' && $node instanceof \OCA\DAV\CalDAV\CalendarHome) ||
			($method === 'REPORT' && $node instanceof \OCA\DAV\CalDAV\Calendar)) {
			$headers = $request->getHeaders();

			if (isset($headers['X-Nc-Show-Deleted']) && $headers['X-Nc-Show-Deleted'] === 'ON') {
				$node->showDeleted();
			}
		}
	}

	/**
	 * We intercept this to handle POST requests on calendars.
	 *
	 * @param RequestInterface $request
	 * @param ResponseInterface $response
	 *
	 * @return void|bool
	 */
	public function httpPost(RequestInterface $request, ResponseInterface $response) {
		return 123;
	}
}
