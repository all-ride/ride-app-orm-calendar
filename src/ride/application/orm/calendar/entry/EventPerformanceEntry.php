<?php

namespace ride\application\orm\calendar\entry;

use ride\application\orm\entry\EventPerformanceEntry as GeneratedEventPerformanceEntry;

use ride\library\StringHelper;

class EventPerformanceEntry extends GeneratedEventPerformanceEntry {

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

    public function getDateString($format = '%x', $separator = ' - ') {
        $dateStart = $this->getDateStart();
        $dateStop = $this->getDateStop();
        $timeStart = $this->getTimeStart();
        $timeStop = $this->getTimeStop();

        $date = strftime($format, $dateStart);
        if ($timeStart !== null) {
            $date .= ' ' . $this->formatTime($timeStart);
        }

        if ($dateStop !== null) {
            $date .= $separator . strftime($format, $dateStop);
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
