<?php

namespace ride\application\orm\calendar\entry;

use ride\application\orm\entry\EventEntry as GeneratedEventEntry;

use ride\library\StringHelper;

class EventEntry extends GeneratedEventEntry {

    public function __toString() {
        return $this->getName();
    }

    public function getTeaserString() {
        $date = $this->getDateString();
        $description = StringHelper::truncate(strip_tags($this->getDescription()));

        $result = $date;
        if ($description) {
            $result .= ': ';
        }
        $result .= $description;

        return $result;
    }

    public function getDateString($format = '%x', $separator = ' - ') {
        $performances = $this->getPerformances();
        if (!$performances) {
            return '';
        }

        $firstDate = null;
        $lastDate = null;

        foreach ($performances as $performance) {
            $date = $performance->getDateStart();
            if ($firstDate === null) {
                $firstDate = $date;
                $lastDate = $date;
            } elseif ($date < $firstDate) {
                $firstDate = $date;
            } elseif ($lastDate < $date) {
                $lastDate = $date;
            }

            $date = $performance->getDateStop();
            if ($date) {
                if ($date < $firstDate) {
                    $firstDate = $date;
                } elseif ($lastDate < $date) {
                    $lastDate = $date;
                }
            }
        }

        $result = strftime($format, $firstDate);
        if ($firstDate != $lastDate) {
            $result .= $separator . strftime($format, $lastDate);
        }

        return $result;
    }

}
