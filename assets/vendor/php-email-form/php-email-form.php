<?php

class PHP_Email_Form {

  public $to;
  public $from_name;
  public $from_email;
  public $subject;
  public $smtp = [];
  public $ajax = false;
  private $message_body = "";

  public function add_message($content, $label = "", $newline = 0) {
    $this->message_body .= "$label: $content\n";
    if($newline > 0) {
      $this->message_body .= str_repeat("\n", $newline);
    }
  }

  public function send() {

    // ✅ Gmail SMTP
    $host = "ssl://smtp.gmail.com";
    $port = 465;
    $username = $this->smtp['username'];
    $password = $this->smtp['password'];

    $smtp = stream_socket_client(
      "$host:$port",
      $errno,
      $errstr,
      30,
      STREAM_CLIENT_CONNECT,
      stream_context_create([
        "ssl" => [
          "verify_peer" => false,
          "verify_peer_name" => false,
          "allow_self_signed" => true
        ]
      ])
    );

    if (!$smtp) {
      return "SMTP Connection Failed: $errstr ($errno)";
    }

    fwrite($smtp, "EHLO localhost\r\n");
    fwrite($smtp, "AUTH LOGIN\r\n");
    fwrite($smtp, base64_encode($username)."\r\n");
    fwrite($smtp, base64_encode($password)."\r\n");

    // ✅ Gmail requires FROM to match login email
    fwrite($smtp, "MAIL FROM:<$username>\r\n");

    // ✅ SEND TO OUTLOOK (works on localhost)
    fwrite($smtp, "RCPT TO:<nimishjoshi@outlook.com>\r\n");

    fwrite($smtp, "DATA\r\n");

    fwrite($smtp, "Subject: {$this->subject}\r\n");

    // ✅ Gmail-safe headers
    fwrite($smtp, "From: Nimish <{$username}>\r\n");
    fwrite($smtp, "Reply-To: {$this->from_email}\r\n");
    fwrite($smtp, "MIME-Version: 1.0\r\n");
    fwrite($smtp, "Content-Type: text/plain; charset=UTF-8\r\n\r\n");

    fwrite($smtp, "{$this->message_body}\r\n.\r\n");

    fwrite($smtp, "QUIT\r\n");
    fclose($smtp);

    return "OK";
  }
}

?>
