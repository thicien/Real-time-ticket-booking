<?php
$plain_password = 'admin'; 
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

echo "Plain Password (Used for Login): " . $plain_password . "\n\n";
echo "--- COPY EVERYTHING BELOW THIS LINE (The whole string) --- \n";
echo $hashed_password . "\n";
echo "---------------------------------------------------------\n";
?>