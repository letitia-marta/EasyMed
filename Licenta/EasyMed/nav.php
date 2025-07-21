<?php
    if (!isset($role) && isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT rol FROM utilizatori WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $role = $user['rol'];
    }
?>

<section class="navigation">
</section> 