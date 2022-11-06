<?php
//header('HTTP/1.1 ' . $code);
echo json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);