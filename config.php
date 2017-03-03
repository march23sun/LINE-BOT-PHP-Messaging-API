<?php

$url = parse_url(getenv("DATABASE_URL"));

return (object) array(
    'db_server' =>$url["host"],
    'db_username' => $url["user"],
    'db_pass' => $url["pass"],
    'db_database' => substr($url["path"], 1),
    'channelID' => '*******',
    'channelAccessToken' => '*******',
    'channelSecret' => '*******',

);

?>