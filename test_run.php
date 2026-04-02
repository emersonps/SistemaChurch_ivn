<?php
require 'src/database/MigrationRunner.php';
require 'config/database.php';

$runner = new MigrationRunner();
var_dump($runner->run());