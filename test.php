<?php
echo "Connection Test OK!";
echo "<br>";
echo "Current Directory: " . __DIR__;
echo "<br>";
echo "Files in directory: ";
print_r(scandir(__DIR__));
?>
