<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'monitoring');
$res = $conn->query("SELECT server_id, stars, has_border, has_bg_color, highlight_color FROM server_rankings");
while($row = $res->fetch_assoc()) print_r($row);
