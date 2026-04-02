<?php
// database/migrations/20260311_160000_add_contact_fields_to_events.php

return [
    "ALTER TABLE events ADD COLUMN address TEXT DEFAULT NULL",
    "ALTER TABLE events ADD COLUMN contact_email VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE events ADD COLUMN contact_phone VARCHAR(20) DEFAULT NULL"
];
