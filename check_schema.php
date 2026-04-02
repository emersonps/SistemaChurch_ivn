<?php
try {
    $db = new PDO('sqlite:database.sqlite');
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='events'");
    $table = $stmt->fetch();
    
    if (!$table) {
        echo "Table 'events' does not exist.\n";
    } else {
        echo "Table 'events' exists. Columns:\n";
        $stmt = $db->query("PRAGMA table_info(events)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo $col['name'] . " (" . $col['type'] . ")\n";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
