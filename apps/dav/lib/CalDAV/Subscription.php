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
namespace OCA\DAV\CalDAV;

class Subscription extends \Sabre\CalDAV\Subscriptions\Subscription {

	/**
	 * restore a previously deleted subscription
	 *
	 * @return void
	 */
	function restore() {
		$this->caldavBackend->restoreSubscription($this->subscriptionInfo['id']);
	}

	/**
	 * delete a subscription for good
	 *
	 * @return void
	 */
	function purge() {
		$this->caldavBackend->purgeSubscription($this->subscriptionInfo['id']);
	}
}
