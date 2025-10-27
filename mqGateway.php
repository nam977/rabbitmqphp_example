<?php
header("Content-Type: application/json; charset=UTF-8");

try{
    $raw = file_get_contents("php://input");
    $input = json_decode($raw, true);

    if (!is_array($input)) {
        throw new Exception("Invalid input");
    }

    $type       = $input['type'] ?? null;
    $session_id = $input['session_id'] ?? null;
    $auth_token = $input['auth_token'] ?? null;

    $needsSession = in_array($type, ['validate_session', 'list_threads', 'create_thread'], true);

    if ($needsSession && (!$session_id || !$auth_token)) {
        echo json_encode(['ok' => false, 'error' => 'Missing session credentials']);
        exit;
    }

    $DATA_DIR   = __DIR__ . '/data';
    $DATA_FILE  = $DATA_DIR . '/threads.json';
    if (!is_dir($DATA_DIR)) mkdir($DATA_DIR, 0755, true);
    if (!file_exists($DATA_FILE)) file_put_contents($DATA_FILE, json_encode([]));

    function read_threads($file){
        $fp = fopen($file, 'r'); if (!$fp) return [];
        flock($fp, LOCK_SH);
        $json = stream_get_contents($fp);
        flock($fp, LOCK_UN); fclose($fp);
        $arr = json_decode($json, true);
        return is_array($arr) ? $arr : [];
    }

    function write_threads($file, $threads){
        $fp = fopen($file, 'c+'); if (!$fp) throw new Exception("Unable to open data file for writing");
        flock($fp, LOCK_EX);
        ftruncate($fp, 0); rewind($fp);
        fwrite($fp, json_encode($threads, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        fflush($fp); flock($fp, LOCK_UN); fclose($fp);
    }

    switch($type){
        case 'validate_session':
            // For simplicity, assume any non-empty session_id and auth_token are valid
            echo json_encode(['ok' => true, 'status' => 'ok', 'session_valid' => true]);
            break;

        case 'list_threads':
            $threads = read_threads($DATA_FILE);
            usort($threads, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
            echo json_encode(['ok' => true, 'status' => 'ok', 'threads' => $threads]);
            break;

        case 'create_thread':
            $title = trim(((string)$input['title'] ?? ''));
            $body  = trim(((string)$input['body'] ?? ''));

            if ($title === '' || $body === '') {
                echo json_encode(['ok' => false, 'error' => 'Title and body are required']);
                break;
            }

            $threads = read_threads($DATA_FILE);
            $newThread = [
                'id'         => bin2hex(random_bytes(16)),
                'title'      => $title,
                'body'       => $body,
                'author'     => $input['username'] ?? 'Anonymous',
                'created_at' => gmdate('c'),
            ];
            $threads[] = $newThread;
            write_threads($DATA_FILE, $threads);
            echo json_encode(['ok' => true, 'created' => true, 'thread' => $newThread]);
            break;

        default:
            http_response_code(400);    
            echo json_encode(['ok' => false, 'message' => 'Unknown request type']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Server error', 'details' => $e->getMessage()]);
}   
