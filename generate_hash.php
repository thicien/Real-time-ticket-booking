<?php
// generate_hash_fix.php

// The new, simple password we will use for admin login
$plain_password = 'admin'; 
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

echo "Plain Password (Used for Login): " . $plain_password . "\n\n";
echo "--- COPY EVERYTHING BELOW THIS LINE (The whole string) --- \n";
echo $hashed_password . "\n";
echo "---------------------------------------------------------\n";
?>