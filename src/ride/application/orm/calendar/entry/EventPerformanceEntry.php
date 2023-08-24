<?php

namespace ride\application\orm\calendar\entry;

use ride\application\orm\entry\EventPerformanceEntry as GeneratedEventPerformanceEntry;

use ride\library\StringHelper;

class EventPerformanceEntry extends GeneratedEventPerformanceEntry {

    public function getNextDate($dateFrom = null) {
        if ($dateFrom === null) {
            $dateFrom = time();
        }

        $dateFrom = mktime(0, 0, 0, date('m', $dateFrom), date('d', $dateFrom), date('Y', $dateFrom));

        $result = false;

        $repeater = $this->getRepeater();
        if (!$repeater) {
            // ne repeated performance, look for the next date
            $dateStart = $this->getDateStart();
            if ($dateStart > $dateFrom) {
                // performance has not begun yet
                $result = $dateStart;
            } else {
                // performance has begun or is passed
                $dateStop = $this->getDateStop();
                if ($dateStop && $dateStop > $dateFrom) {
                    // it's a period and we're in the middle of it, so next date is tomorrow
                    $result = $dateFrom + EventRepeaterEntry::DAY;
                } else {
                    // performance is passed, no next date
                }
            }
        } else {
            // repeated event, get next date from the repeater
            $dates = $repeater->getDates($dateFrom);
            $result = reset($dates);
        }

        return $result;
    }

    public function getTeaserString() {
        $result = StringHelper::truncate(strip_tags($this->getDescription()));

        $location = $this->getLocation();
        $address = $this->getAddress();
        if ($location || $address) {
            $result .= '(';
            if ($location) {
                $result .= strip_tags($location);
                if ($address) {
                    $result .= ', ';
                }
            }
            if ($address) {
                $result .= strip_tags($address);
            }
            $result .= ')';
        }

        return $result;
    }

    public function getDateString($format = 'd/m/Y', $separator = ' - ') {
        $dateStart = $this->getDateStart();
        $dateStop = $this->getDateStop();
        $timeStart = $this->getTimeStart();
        $timeStop = $this->getTimeStop();

        $date = date($format, $dateStart);
        if ($timeStart !== null) {
            $date .= ' ' . $this->formatTime($timeStart);
        }

        if ($dateStop !== null) {
            $date .= $separator . date($format, $dateStop);
            if ($timeStop !== null) {
                $date .= ' ' . $this->formatTime($timeStop);
            }
        } elseif ($timeStop !== null) {
            $date .= $separator . $this->formatTime($timeStop);
        }

        return $date;
    }

    /**
     * @deprecated
     */
    public function getDate() {
        return $this->getDateString();
    }

    protected function formatTime($value) {
        if (!is_numeric($value)) {
            return $value;
        }

        $hours = floor($value / 3600);
        $value = $value % 3600;
        $minutes = floor($value / 60);

        return $hours . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }

    public function isPeriod() {
        return $this->getDateStop() ? true : false;
    }

}
