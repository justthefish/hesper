<?php
/**
 * @project    Hesper Framework
 * @author     Alex Gorbylev
 * @originally onPHP Framework
 * @originator Anton E. Lebedevich
 */
namespace Hesper\Main\Base;

use Hesper\Core\Base\Date;
use Hesper\Core\Base\Timestamp;
use Hesper\Core\Exception\WrongArgumentException;

/**
 * Calendar month representation splitted by weeks.
 * @package Hesper\Main\Base
 */
final class CalendarMonthWeekly {

	private $monthRange = null;
	private $fullRange  = null;
	private $fullLength = null;

	private $weeks = [];
	private $days  = [];

	public function __construct(Date $base, $weekStart = Timestamp::WEEKDAY_MONDAY) {
		$firstDayOfMonth = Date::create($base->getYear() . '-' . $base->getMonth() . '-01');

		$lastDayOfMonth = Date::create($base->getYear() . '-' . $base->getMonth() . '-' . date('t', $base->toStamp()));

		$start = $firstDayOfMonth->getFirstDayOfWeek($weekStart);

		$end = $lastDayOfMonth->getLastDayOfWeek($weekStart);

		$this->monthRange = DateRange::create()
		                             ->lazySet($firstDayOfMonth, $lastDayOfMonth);

		$this->fullRange = DateRange::create()
		                            ->lazySet($start, $end);

		$rawDays = $this->fullRange->split();
		$this->fullLength = 0;

		foreach ($rawDays as $rawDay) {
			$day = CalendarDay::create($rawDay->toStamp());

			if ($this->monthRange->contains($day)) {
				$day->setOutside(false);
			} else {
				$day->setOutside(true);
			}

			$this->days[$day->toDate()] = $day;

			$weekNumber = floor($this->fullLength / 7);

			if (!isset($this->weeks[$weekNumber])) {
				$this->weeks[$weekNumber] = CalendarWeek::create();
			}

			$this->weeks[$weekNumber]->addDay($day);
			++$this->fullLength;
		}

		++$this->fullLength;
	}

	/**
	 * @return CalendarMonthWeekly
	 **/
	public static function create(Date $base, $weekStart = Timestamp::WEEKDAY_MONDAY) {
		return new self($base, $weekStart);
	}

	public function getWeeks() {
		return $this->weeks;
	}

	public function getDays() {
		return $this->days;
	}

	/**
	 * @return DateRange
	 **/
	public function getFullRange() {
		return $this->fullRange;
	}

	public function getFullLength() {
		return $this->fullLength;
	}

	/**
	 * @return DateRange
	 **/
	public function getMonthRange() {
		return $this->monthRange;
	}

	/**
	 * @throws WrongArgumentException
	 * @return CalendarMonthWeekly
	 **/
	public function setSelected(Date $day) {
		if (!isset($this->days[$day->toDate()])) {
			throw new WrongArgumentException($day->toDate() . ' not in calendar');
		}

		$this->days[$day->toDate()]->setSelected(true);

		return $this;
	}

	/**
	 * @return Date
	 **/
	public function getNextMonthBase() {
		return $this->monthRange->getEnd()
		                        ->spawn('+1 day');
	}

	/**
	 * @return Date
	 **/
	public function getPrevMonthBase() {
		return $this->monthRange->getStart()
		                        ->spawn('-1 day');
	}

	/**
	 * @return Date
	 **/
	public function getBase() {
		return $this->monthRange->getStart();
	}
}
