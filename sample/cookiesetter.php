<?php
function create_my_session_cookie(array $my_session): void{
    $expires = strtotime($my_session['expires'] ?? '+1 hour');
    $cookieParams = [
        'expires' => $expires,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ];

    if (!empty($my_session['session_id'])) {
        setcookie('session_id', $my_session['session_id'], $cookieParams);
    }   

    if(!empty($my_session['auth_token'])) {
        setcookie('auth_token', $my_session['auth_token'], $cookieParams);
    }
}

function erase_my_session_cookies(): void {
    $time_since_creation = time() - 3600; // 1 hour ago
    foreach(['session_id', 'auth_token'] as $mycookie){
        setcookie($mycookie, '', [
            'expires' => $time_since_creation,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}
?>