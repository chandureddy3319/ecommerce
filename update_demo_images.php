<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

updateDemoImages($conn);
echo '<h2>Demo images updated successfully!</h2><a href="index.php">Go to Home</a>'; 