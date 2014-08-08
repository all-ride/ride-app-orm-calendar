<?php

namespace ride\application\orm\calendar\entry;

use ride\application\orm\entry\EventPerformanceEntry as GeneratedEventPerformanceEntry;

class EventPerformanceEntry extends GeneratedEventPerformanceEntry {

    public function isPeriod() {
        return $this->getDateStop() ? true : false;
    }

}
