<?php

namespace ride\application\orm\calendar\entry;

use ride\application\orm\entry\EventPerformanceEntry as GeneratedEventPerformanceEntry;

class EventPerformanceEntry extends GeneratedEventPerformanceEntry {

    public function getDate() {
        $dateStart = $this->getDateStart();
        $dateStop = $this->getDateStop();
        $timeStart = $this->getTimeStart();
        $timeStop = $this->getTimeStop();

        $date = date('Y-m-d', $dateStart);
        if ($timeStart !== null) {
            $date .= ' ' . $this->formatTime($timeStart);
        }

        if ($dateStop !== null) {
            $date .= ' - ' . date('Y-m-d', $dateStop);
            if ($timeStop !== null) {
                $date .= ' ' . $this->formatTime($timeStop);
            }
        } elseif ($timeStop !== null) {
            $date .= ' - ' . $this->formatTime($timeStop);
        }

        return $date;
    }

    protected function formatTime($value) {
        if (!is_numeric($value)) {
            return $value;
        }

        $hours = floor($value / 3600);
        $value = $value % 3600;
        $minutes = floor($value / 60);
        $seconds = $value % 60;

        return $hours . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }

    public function isPeriod() {
        return $this->getDateStop() ? true : false;
    }

}
