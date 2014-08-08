<?php

namespace ride\application\orm\calendar\entry;

use ride\application\orm\entry\EventEntry as GeneratedEventEntry;

class EventEntry extends GeneratedEventEntry {

    public function __toString() {
        return $this->getName();
    }

}
