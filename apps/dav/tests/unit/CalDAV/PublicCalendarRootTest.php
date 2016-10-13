<?php

namespace OCA\DAV\Tests\unit\CalDAV;

use OCA\DAV\CalDAV\Activity\Backend as ActivityBackend;
use OCA\DAV\CalDAV\Calendar;
use OCA\DAV\Connector\Sabre\Principal;
use OCP\Activity\IManager as IActivityManager;
use OCP\IGroupManager;
use OCP\IL10N;
use OCA\DAV\CalDAV\CalDavBackend;
use OCA\DAV\CalDAV\PublicCalendarRoot;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Security\ISecureRandom;
use Test\TestCase;

/**
 * Class PublicCalendarRootTest
 *
 * @group DB
 *
 * @package OCA\DAV\Tests\unit\CalDAV
 */
class PublicCalendarRootTest extends TestCase {

	const UNIT_TEST_USER = 'principals/users/caldav-unit-test';
	/** @var CalDavBackend */
	private $backend;
	/** @var PublicCalendarRoot */
	private $publicCalendarRoot;
	/** @var IL10N */
	private $l10n;
	/** @var Principal|\PHPUnit_Framework_MockObject_MockObject */
	private $principal;
	/** @var IUserManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $userManager;
	/** @var IGroupManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $groupManager;
	/** @var IActivityManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $activityManager;
	/** @var IUserSession|\PHPUnit_Framework_MockObject_MockObject */
	protected $userSession;
	/** @var ActivityBackend|\PHPUnit_Framework_MockObject_MockObject */
	protected $activityBackend;

	/** @var ISecureRandom */
	private $random;

	public function setUp() {
		parent::setUp();

		$db = \OC::$server->getDatabaseConnection();
		$this->principal = $this->createMock('OCA\DAV\Connector\Sabre\Principal');
		$this->userManager = $this->createMock(IUserManager::class);
		$groupManager = $this->createMock(IGroupManager::class);
		$activityManager = $this->createMock(IActivityManager::class);
		$userSession = $this->createMock(IUserSession::class);
		$this->random = \OC::$server->getSecureRandom();

		$this->backend = new CalDavBackend(
			$db,
			$this->principal,
			$this->userManager,
			$groupManager,
			$this->random,
			$activityManager,
			$userSession
		);

		$this->activityBackend = $this->createMock(ActivityBackend::class);
		$this->invokePrivate($this->backend, 'activityBackend', [$this->activityBackend]);

		$this->publicCalendarRoot = new PublicCalendarRoot($this->backend);

		$this->l10n = $this->getMockBuilder('\OCP\IL10N')
			->disableOriginalConstructor()->getMock();
	}

	public function tearDown() {
		parent::tearDown();

		if (is_null($this->backend)) {
			return;
		}
		$books = $this->backend->getCalendarsForUser(self::UNIT_TEST_USER);
		foreach ($books as $book) {
			$this->backend->deleteCalendar($book['id']);
		}
	}

	public function testGetName() {
		$name = $this->publicCalendarRoot->getName();
		$this->assertEquals('public-calendars', $name);
	}

	public function testGetChild() {
		$this->activityBackend->expects($this->exactly(1))
			->method('addCalendar');
		$this->activityBackend->expects($this->never())
			->method('updateCalendar');
		$this->activityBackend->expects($this->never())
			->method('deleteCalendar');
		$this->activityBackend->expects($this->never())
			->method('updateCalendarShares');

		$calendar = $this->createPublicCalendar();

		$publicCalendars = $this->backend->getPublicCalendars();
		$this->assertEquals(1, count($publicCalendars));
		$this->assertEquals(true, $publicCalendars[0]['{http://owncloud.org/ns}public']);

		$publicCalendarURI = $publicCalendars[0]['uri'];

		$calendarResult = $this->publicCalendarRoot->getChild($publicCalendarURI);
		$this->assertEquals($calendar, $calendarResult);
	}

	public function testGetChildren() {
		$this->activityBackend->expects($this->exactly(1))
			->method('addCalendar');
		$this->activityBackend->expects($this->never())
			->method('updateCalendar');
		$this->activityBackend->expects($this->never())
			->method('deleteCalendar');
		$this->activityBackend->expects($this->never())
			->method('updateCalendarShares');

		$this->createPublicCalendar();

		$publicCalendars = $this->backend->getPublicCalendars();

		$calendarResults = $this->publicCalendarRoot->getChildren();

		$this->assertEquals(1, count($calendarResults));
		$this->assertEquals(new Calendar($this->backend, $publicCalendars[0], $this->l10n), $calendarResults[0]);
	}

	/**
	 * @return Calendar
	 */
	protected function createPublicCalendar() {
		$this->backend->createCalendar(self::UNIT_TEST_USER, 'Example', []);

		$calendarInfo = $this->backend->getCalendarsForUser(self::UNIT_TEST_USER)[0];
		$calendar = new Calendar($this->backend, $calendarInfo, $this->l10n);
		$publicUri = $calendar->setPublishStatus(true);

		$calendarInfo = $this->backend->getPublicCalendar($publicUri);
		$calendar = new Calendar($this->backend, $calendarInfo, $this->l10n);

		return $calendar;
	}

}
