<?php
    function hasRole($roleName) {
        return isset($_SESSION['user']['role']) && 
            in_array($roleName, $_SESSION['user']['role']);
    }
?>