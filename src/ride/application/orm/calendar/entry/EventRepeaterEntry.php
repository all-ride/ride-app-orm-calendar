<?php

namespace ride\application\orm\calendar\entry;

use ride\application\orm\entry\EventRepeaterEntry as GeneratedEventRepeaterEntry;

class EventRepeaterEntry extends GeneratedEventRepeaterEntry {

    const DAY = 86400;

    const WEEK = 604800;

    const MODE_DAILY = 'daily';

    const MODE_WEEKLY = 'weekly';

    const MODE_MONTHLY = 'monthly';

    const MODE_MONTHLY_DAY_OF_WEEK = 'week';

    const MODE_MONTHLY_DAY_OF_MONTH = 'month';

    const MODE_YEARLY = 'yearly';

    public function equals($repeater) {
        if (!$repeater instanceof self) {
            return false;
        }

        if ($this->mode !== $repeater->mode) {
            return false;
        }
        if ($this->modeDetail !== $repeater->modeDetail) {
            return false;
        }
        if ($this->step !== $repeater->step) {
            return false;
        }
        if ($this->occurences !== $repeater->occurences) {
            return false;
        }
        if ($this->dateUntil !== $repeater->dateUntil) {
            return false;
        }

        return true;
    }

    public function getDates($dateFrom) {
        $dates = array();
        $numDates = 0;

        $mode = $this->getMode();
        $modeDetail = $this->getModeDetail();
        $step = $this->getStep();
        $dateUntil = $this->getDateUntil();
        $occurences = $this->getOccurences();

        switch ($mode) {
            case self::MODE_DAILY:
                $offset = self::DAY * $step;
                if ($dateUntil) {
                    $dates = $this->generateDailyDatesWithDateUntil($dateFrom, $offset, $dateUntil);
                } else {
                    $dates = $this->generateDailyDatesWithOccurences($dateFrom, $offset, $occurences);
                }

                break;
            case self::MODE_WEEKLY:
                $offset = self::WEEK * $step;
                if ($modeDetail) {
                    $days = explode(',', $modeDetail);
                } else {
                    $days = array(date('N', $dateFrom));
                }

                if ($dateUntil) {
                    $dates = $this->generateWeeklyDatesWithDateUntil($dateFrom, $offset, $days, $dateUntil);
                } else {
                    $dates = $this->generateWeeklyDatesWithOccurences($dateFrom, $offset, $days, $occurences);
                }

                break;
            case self::MODE_MONTHLY:
                if ($dateUntil) {
                    $dates = $this->generateMonthlyDatesWithDateUntil($dateFrom, $step, $modeDetail, $dateUntil);
                } else {
                    $dates = $this->generateMonthlyDatesWithOccurences($dateFrom, $step, $modeDetail, $occurences);
                }
                break;
            case self::MODE_YEARLY:
                if ($dateUntil) {
                    $dates = $this->generateYearlyDatesWithDateUntil($dateFrom, $step, $dateUntil);
                } else {
                    $dates = $this->generateYearlyDatesWithOccurences($dateFrom, $step, $occurences);
                }

                break;
            default:
                break;
        }

        return $dates;
    }

    /**
     * @param integer $dateFrom Timestamp of the start date
     * @param integer $offset Number of seconds to add each loop
     * @param integer $dateUntil Timestamp of the stop date
     * @return array Array with timestamps
     */
    protected function generateDailyDatesWithDateUntil($dateFrom, $offset, $dateUntil) {
        $dates = array();

        while ($dateFrom < $dateUntil) {
            $dates[] = $dateFrom;

            $dateFrom += $offset;
        }

        return $dates;
    }

    /**
     * @param integer $dateFrom Timestamp of the start date
     * @param integer $offset Number of seconds to add each loop
     * @param integer $occurences Number of dates to add
     * @return array Array with timestamps
     */
    protected function generateDailyDatesWithOccurences($dateFrom, $offset, $occurences) {
        $dates = array();
        $occured = 0;

        while ($occured < $occurences) {
            $dates[] = $dateFrom;
            $occured++;

            $dateFrom += $offset;
        }

        return $dates;
    }

    /**
     * @param integer $dateFrom Timestamp of the start date
     * @param integer $offset Number of seconds to add each loop
     * @param array $days Days of the week which should be generated (1-7)
     * @param integer $dateUntil Timestamp of the stop date
     * @return array Array with timestamps
     */
    protected function generateWeeklyDatesWithDateUntil($dateFrom, $offset, array $days, $dateUntil) {
        $dates = array();

        $weekday = date('N', $dateFrom);
        $start = $dateFrom - (($weekday - 1) * self::DAY);

        while ($start < $dateUntil) {
            foreach ($days as $day) {
                $date = $start + (($day - 1) * self::DAY);
                if ($date < $dateFrom) {
                    continue;
                }

                if ($date >= $dateUntil) {
                    break;
                }

                $dates[] = $date;
            }

            $start += $offset;
        }

        return $dates;
    }

    /**
     * @param integer $dateFrom Timestamp of the start date
     * @param integer $offset Number of seconds to add each loop
     * @param array $days Days of the week which should be generated (1-7)
     * @param integer $occurences Number of dates to add
     * @return array Array with timestamps
     */
    protected function generateWeeklyDatesWithOccurences($dateFrom, $offset, array $days, $occurences) {
        $dates = array();
        $occured = 0;

        $weekday = date('N', $dateFrom);
        $start = $dateFrom - (($weekday - 1) * self::DAY);

        while ($occured < $occurences) {
            foreach ($days as $day) {
                $date = $start + (($day - 1) * self::DAY);
                if ($date < $dateFrom) {
                    continue;
                }

                $dates[] = $date;

                $occured++;
                if ($occured >= $occurences) {
                    break 2;
                }
            }

            $start += $offset;
        }

        return $dates;
    }

    /**
     * @param integer $dateFrom Timestamp of the start date
     * @param integer $step Number of years to add each loop
     * @param integer $dateUntil Timestamp of the stop date
     * @return array Array with timestamps
     */
    protected function generateMonthlyDatesWithDateUntil($dateFrom, $step, $mode, $dateUntil) {
        $dates = array();

        $hour = date('H', $dateFrom);
        $minute = date('i', $dateFrom);
        $second = date('s', $dateFrom);
        $month = date('n', $dateFrom);
        $day = date('j', $dateFrom);
        $year = date('Y', $dateFrom);

        switch ($mode) {
            case self::MODE_MONTHLY_DAY_OF_WEEK:
                $generateWeekDay = date('N', $dateFrom);
                $generateWeek = $this->getMonthWeekNumber($dateFrom);
                $skip = false;

                while ($dateFrom < $dateUntil) {
                    if (!$skip) {
                        $dates[] = $dateFrom;
                    } else {
                        $skip = false;
                    }

                    $this->addMonth($month, $year, $step);

                    $dateFrom = mktime($hour, $minute, $second, $month, 1, $year);
                    $dateFrom = $this->getNextWeekDay($generateWeekDay, $dateFrom);
                    $dateFrom += ($generateWeek - 1) * self::WEEK;

                    if (date('n', $dateFrom) != $month) {
                        $skip = true;
                    }
                }

                break;
            case self::MODE_MONTHLY_DAY_OF_MONTH:
                while ($dateFrom < $dateUntil) {
                    $dates[] = $dateFrom;

                    $this->addMonth($month, $year, $step);

                    $dateFrom = mktime($hour, $minute, $second, $month, $day, $year);
                }

                break;
            default:
                break;
        }

        return $dates;
    }

    /**
     * @param integer $dateFrom Timestamp of the start date
     * @param integer $step Number of years to add each loop
     * @param integer $occurences Number of dates to add
     * @return array Array with timestamps
     */
    protected function generateMonthlyDatesWithOccurences($dateFrom, $step, $mode, $occurences) {
        $dates = array(
            $dateFrom
        );
        $occured = 1;

        $hour = date('H', $dateFrom);
        $minute = date('i', $dateFrom);
        $second = date('s', $dateFrom);
        $month = date('n', $dateFrom);
        $day = date('j', $dateFrom);
        $year = date('Y', $dateFrom);

        switch ($mode) {
            case self::MODE_MONTHLY_DAY_OF_WEEK:
                $generateWeekDay = date('N', $dateFrom);
                $generateWeek = $this->getMonthWeekNumber($dateFrom);

                while ($occured < $occurences) {
                    $this->addMonth($month, $year, $step);

                    $dateFrom = mktime($hour, $minute, $second, $month, 1, $year);
                    $dateFrom = $this->getNextWeekDay($generateWeekDay, $dateFrom);
                    $dateFrom += ($generateWeek - 1) * self::WEEK;

                    if (date('n', $dateFrom) != $month) {
                        continue;
                    }

                    $dates[] = $dateFrom;
                    $occured++;
                }

                break;
            case self::MODE_MONTHLY_DAY_OF_MONTH:
                while ($occured < $occurences) {
                    $this->addMonth($month, $year, $step);

                    $dateFrom = mktime($hour, $minute, $second, $month, $day, $year);

                    $dates[] = $dateFrom;
                    $occured++;
                }

                break;
            default:
                break;
        }

        return $dates;
    }

    protected function addMonth(&$month, &$year, $step = 1) {
        $month += $step;
        while ($month > 12) {
            $month -= 12;
            $year++;
        }
    }

    protected function getMonthWeekNumber($date) {
        $month = date('n', $date);
        $week = 0;

        do {
            $date -= self::WEEK;
            $week++;
        } while (date('n', $date) == $month);

        return $week;
    }

    protected function getNextWeekDay($generateWeekDay, $date) {
        $weekDay = date('N', $date);
        if ($weekDay <= $generateWeekDay) {
            $date += ($generateWeekDay - $weekDay) * self::DAY;
        } else {
            $date += ((7 - $weekDay) + $generateWeekDay) * self::DAY;
        }

        return $date;
    }

    /**
     * @param integer $dateFrom Timestamp of the start date
     * @param integer $step Number of years to add each loop
     * @param integer $dateUntil Timestamp of the stop date
     * @return array Array with timestamps
     */
    protected function generateYearlyDatesWithDateUntil($dateFrom, $step, $dateUntil) {
        $dates = array();

        $hour = date('H', $dateFrom);
        $minute = date('i', $dateFrom);
        $second = date('s', $dateFrom);
        $month = date('n', $dateFrom);
        $day = date('j', $dateFrom);
        $year = date('Y', $dateFrom);

        while ($dateFrom < $dateUntil) {
            $dates[] = $dateFrom;

            $year += $step;

            $dateFrom = mktime($hour, $minute, $second, $month, $day, $year);
        }

        return $dates;
    }

    /**
     * @param integer $dateFrom Timestamp of the start date
     * @param integer $step Number of years to add each loop
     * @param integer $occurences Number of dates to add
     * @return array Array with timestamps
     */
    protected function generateYearlyDatesWithOccurences($dateFrom, $step, $occurences) {
        $dates = array();
        $occured = 0;

        $hour = date('H', $dateFrom);
        $minute = date('i', $dateFrom);
        $second = date('s', $dateFrom);
        $month = date('n', $dateFrom);
        $day = date('j', $dateFrom);
        $year = date('Y', $dateFrom);

        while ($occured < $occurences) {
            $dates[] = $dateFrom;
            $occured++;

            $year += $step;

            $dateFrom = mktime($hour, $minute, $second, $month, $day, $year);
        }

        return $dates;
    }

}
