<?php 
define("SQL_USER", "zm");
define("SQL_PASS", "mellamoandres"); 
define("SQL_SERVERNAME", "localhost");
define("SQL_DBNAME", "telegram_api");


function db_getChats() {
    $conn = new mysqli(SQL_SERVERNAME, SQL_USER, SQL_PASS, SQL_DBNAME);
    if ($conn->connect_error) {
        http_response_code(500); die("error connectSQL: " . $conn->connect_error);
    } 
    $sql = 'SELECT * FROM chats';
    $result = $conn->query($sql);
    $chats = array();
    while ( $row = $result->fetch_array(MYSQLI_ASSOC) ) {
        $chats[] = $row['id'];
    }
    $conn->close();
    return $chats;
}


function db_getGlobal($id) {
    $conn = new mysqli(SQL_SERVERNAME, SQL_USER, SQL_PASS, SQL_DBNAME);
    if ($conn->connect_error) {
        http_response_code(500); die("error connectSQL: " . $conn->connect_error);
    } 
    $sql = 'SELECT * FROM globals WHERE id LIKE "'.$id.'"';
    $result = $conn->query($sql);
    if ( $result->num_rows == 0 ) { 
        return '';     // devuelvo empty array
    } 
    $result = $result->fetch_array(MYSQLI_ASSOC);
    $conn->close();
    return $result['value'];
}


function db_setGlobal($id, $value) {
    $conn = new mysqli(SQL_SERVERNAME, SQL_USER, SQL_PASS, SQL_DBNAME);
    if ($conn->connect_error) {
        http_response_code(500); die("error connectSQL: " . $conn->connect_error);
    } 
    $sql = 'UPDATE globals SET value="' . $value . '" WHERE id LIKE "' . $id . '"';
    if ( !$conn->query($sql) ) { http_response_code(500); die(); }
    if ( $conn->affected_rows == 0 ) { 
        //http_response_code(500); die();
    } 
    $conn->close();
}


function db_getLastEvent() {
    $conn = new mysqli(SQL_SERVERNAME, SQL_USER, SQL_PASS, SQL_DBNAME);
    if ($conn->connect_error) {
        http_response_code(500); die("error connectSQL: " . $conn->connect_error);
    } 
    $sql = 'SELECT * FROM zm_events ORDER BY id DESC LIMIT 1';
    $result = $conn->query($sql);
    if ( $result->num_rows == 0 ) { 
        return '';     // devuelvo empty array
    } 
    $result = $result->fetch_array(MYSQLI_ASSOC);
    $conn->close();
    return $result;
}


function db_getZmEvent($event_id) {
    $conn = new mysqli(SQL_SERVERNAME, SQL_USER, SQL_PASS, SQL_DBNAME);
    if ($conn->connect_error) {
        http_response_code(500); die("error connectSQL: " . $conn->connect_error);
    } 
    $sql = 'SELECT * FROM zm_events WHERE id = '.$event_id;
    $result = $conn->query($sql);
    if ( $result->num_rows == 0 ) { 
        return false;     // devuelvo empty array
    } 
    $result = $result->fetch_array(MYSQLI_ASSOC);
    $conn->close();
    return $result;
}


function db_addZmEvent($event) {
    $conn = new mysqli(SQL_SERVERNAME, SQL_USER, SQL_PASS, SQL_DBNAME);
    if ($conn->connect_error) {
        http_response_code(500); die("error connectSQL: " . $conn->connect_error);
    } 
    $sql = 'INSERT INTO zm_events (id, start_time, end_time, tl_video_id, tl_snap_id) VALUES 
        ('.$event['id'].',"'.$event['start_time'].'","'.$event['end_time'].'","'.$event['tl_video_id'].'","'.$event['tl_snap_id'].'")'; 
    if ( !$conn->query($sql) ) { http_response_code(500); die(); }
    $conn->close();
}


function db_addZmEventVideo($event_id, $video_id) {
    $conn = new mysqli(SQL_SERVERNAME, SQL_USER, SQL_PASS, SQL_DBNAME);
    if ($conn->connect_error) {
        http_response_code(500); die("error connectSQL: " . $conn->connect_error);
    } 
    $sql = 'UPDATE zm_events SET tl_video_id="'. $video_id . '" WHERE id LIKE "' . $event_id . '"';
    if ( !$conn->query($sql) ) { http_response_code(500); die(); }
    $conn->close();
}


function db_getZmEventVideo($event_id) {
    $conn = new mysqli(SQL_SERVERNAME, SQL_USER, SQL_PASS, SQL_DBNAME);
    if ($conn->connect_error) {
        http_response_code(500); die("error connectSQL: " . $conn->connect_error);
    } 
    $sql = 'SELECT * FROM zm_events WHERE id LIKE "'.$event_id.'"';
    $result = $conn->query($sql);
    if ( $result->num_rows == 0 ) { 
        return '';     // devuelvo empty array
    } 
    $result = $result->fetch_array(MYSQLI_ASSOC);
    $conn->close();
    return $result['tl_video_id'];
}


function db_userExists($user_id) {
    $conn = new mysqli(SQL_SERVERNAME, SQL_USER, SQL_PASS, SQL_DBNAME);
    if ($conn->connect_error) {
        http_response_code(500); die("error connectSQL: " . $conn->connect_error);
    } 
    $sql = 'SELECT * FROM users WHERE id LIKE "'.$user_id.'"';
    $result = $conn->query($sql);
    return ( $result->num_rows != 0 );
}


function db_addUser($user_id) {
    $conn = new mysqli(SQL_SERVERNAME, SQL_USER, SQL_PASS, SQL_DBNAME);
    if ($conn->connect_error) {
        http_response_code(500); die("error connectSQL: " . $conn->connect_error);
    } 
    $sql = 'INSERT INTO users (id, can_add_users) VALUES ('.$user_id.',"'.'0'.'")'; 
    if ( !$conn->query($sql) ) { http_response_code(500); die(); }
    $conn->close();
}


function db_addChat($chat_id, $user_id) {
    $conn = new mysqli(SQL_SERVERNAME, SQL_USER, SQL_PASS, SQL_DBNAME);
    if ($conn->connect_error) {
        http_response_code(500); die("error connectSQL: " . $conn->connect_error);
    } 
    $sql = 'INSERT INTO chats (id, user_id) VALUES ('.$chat_id.','.$user_id.')'; 
    if ( !$conn->query($sql) ) { http_response_code(500); die(); }
    $conn->close();
}


function db_delChat($chat_id) {
    $conn = new mysqli(SQL_SERVERNAME, SQL_USER, SQL_PASS, SQL_DBNAME);
    if ($conn->connect_error) {
        http_response_code(500); die("error connectSQL: " . $conn->connect_error);
    } 
    $sql = 'DELETE FROM chats WHERE id = '.$chat_id; 
    if ( !$conn->query($sql) ) { http_response_code(500); die(); }
    $conn->close();
}


function db_chatExists($chat_id) {
    $conn = new mysqli(SQL_SERVERNAME, SQL_USER, SQL_PASS, SQL_DBNAME);
    if ($conn->connect_error) {
        http_response_code(500); die("error connectSQL: " . $conn->connect_error);
    } 
    $sql = 'SELECT * FROM chats WHERE id = '.$chat_id;
    $result = $conn->query($sql);
    $conn->close();
    return ($result->num_rows != 0);
}
