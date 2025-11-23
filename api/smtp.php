<?php
class SimpleSMTP {
    private $host;
    private $port;
    private $username;
    private $password;
    private $socket;

    public function __construct() {
        global $pdo;
        if (!isset($pdo)) {
            require_once __DIR__ . '/../config/db.php';
        }
        
        $stmt = $pdo->query("SELECT * FROM settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $this->host = $settings['smtp_host'] ?? 'mail.neatech.ar';
        $this->port = $settings['smtp_port'] ?? 587;
        $this->username = $settings['smtp_user'] ?? 'notificacionesngestion@neatech.ar';
        $this->password = $settings['smtp_pass'] ?? 'diorq^p?ClW_NX2q';
    }

    public function send($to, $subject, $body, $ics = null) {
        $this->connect();
        $this->auth();
        
        $boundary = md5(time());
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "From: Cafe Pelotero <{$this->username}>\r\n";
        $headers .= "To: <$to>\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

        $message = "--$boundary\r\n";
        $message .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= "$body\r\n";

        if ($ics) {
            $message .= "--$boundary\r\n";
            $message .= "Content-Type: text/calendar; name=\"invite.ics\"; method=REQUEST\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "Content-Disposition: attachment; filename=\"invite.ics\"\r\n\r\n";
            $message .= chunk_split(base64_encode($ics)) . "\r\n";
        }

        $message .= "--$boundary--";

        $this->sendCommand("MAIL FROM: <{$this->username}>");
        $this->sendCommand("RCPT TO: <$to>");
        $this->sendCommand("DATA");
        $this->sendCommand($headers . "\r\n" . $message . "\r\n.");
        $this->sendCommand("QUIT");
        
        fclose($this->socket);
        return true;
    }

    private function connect() {
        // Connect via TCP first
        $this->socket = fsockopen("tcp://{$this->host}", $this->port, $errno, $errstr, 10);
        if (!$this->socket) throw new Exception("Connection failed: $errstr");
        
        $this->getResponse();
        $this->sendCommand("EHLO {$this->host}");
        
        // STARTTLS
        $this->sendCommand("STARTTLS");
        if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new Exception("TLS negotiation failed");
        }
        
        $this->sendCommand("EHLO {$this->host}");
    }

    private function auth() {
        $this->sendCommand("AUTH LOGIN");
        $this->sendCommand(base64_encode($this->username));
        $this->sendCommand(base64_encode($this->password));
    }

    private function sendCommand($cmd) {
        fwrite($this->socket, $cmd . "\r\n");
        return $this->getResponse();
    }

    private function getResponse() {
        $response = "";
        while ($str = fgets($this->socket, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        return $response;
    }
}

function generateICS($start, $end, $summary, $description) {
    $start_str = gmdate('Ymd\THis\Z', strtotime($start));
    $end_str = gmdate('Ymd\THis\Z', strtotime($end));
    
    return "BEGIN:VCALENDAR\r\n" .
           "VERSION:2.0\r\n" .
           "PRODID:-//Cafe Pelotero//NONSGML//EN\r\n" .
           "BEGIN:VEVENT\r\n" .
           "UID:" . md5($start . $summary) . "@cafepelotero.com\r\n" .
           "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n" .
           "DTSTART:$start_str\r\n" .
           "DTEND:$end_str\r\n" .
           "SUMMARY:$summary\r\n" .
           "DESCRIPTION:$description\r\n" .
           "END:VEVENT\r\n" .
           "END:VCALENDAR";
}
?>
