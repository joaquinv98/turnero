<?php
$hash = '$10$c3aGm2CiFlm.ulKyOIOpXOrl8pOZROtWTHvrrytQT2/V9WMbkJhtK';
$pass = 'admin123';

if (password_verify($pass, $hash)) {
    echo "MATCH";
} else {
    echo "NO MATCH";
}
?>
