<?php
# http://stackoverflow.com/a/24835967/1163786
?>

<!DOCTYPE html>
<html lang="en-US">
    <head>
        <title>Lucky Cloud</title>
    </head>

    <body>
        <?php
            date_default_timezone_set('America/Los_Angeles');
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
            set_time_limit(0);

            $bot = array(
                "Host"     => "kornbluth.freenode.net", #"underworld2.no.quakenet.org", #irc.quakenet.org",
                "Channels" => ["#testerchan"],
                "Nick"     => "Tester7888",
                "Ident"    => "Tester7888",
                "Real"     => "Susi Q",
                "Port"     => 6667
            );
        ?>

        <p>
            Server: <?php echo $bot["Host"]; ?><br />
            Channel(s): <?php foreach($bot["Channels"] as $key => $channel) { echo $channel; } ?><br />
            Port: <?php echo $bot["Port"]; ?><br />
            ___________________________________________________________________________________________________________________<br />
        </p>

        <?php
            global $socket;

            function sendData($cmd, $msg = null) {
                global $socket;
                if($msg == null) {
                    fputs($socket, $cmd."\r\n");
                    echo "<strong>".$cmd."</strong><br />";
                } else {
                    fputs($socket, $cmd." ".$msg."\r\n");
                    echo "<strong>".$cmd." ".$msg."</strong><br />";
                }
            }

            $socket = fsockopen($bot["Host"], $bot["Port"], $error1, $error2);
            if(!$socket) {
                echo 'Crap! fsockopen failed. Details: ' . $error1 . ': ' . $error2;
            }

            sendData("NICK", $bot["Nick"]);
            sendData("USER", $bot["Ident"]." ".$bot["Host"]." ".$bot["Real"]);

            $join_at_start = true;

            $buffer = "";

            while (!feof($socket)) {
                $buffer = trim(fgets($socket, 128));
                echo date('H:i')." ".nl2br($buffer)."<br/>";
                flush();

                # Ping <-> Pong
                if(substr($buffer, 0, 6) == "PING :") {
                    fputs($socket, "PONG :".substr($buffer, 6)."\r\n");
                    echo $buffer;
                    flush();
                }

                // break out of while, 0 bytes
               /* $stream_meta_data = stream_get_meta_data($socket);
                if($stream_meta_data['unread_bytes'] <= 0) {
                    break;
                }*/

                # join only one time
                if($join_at_start === true && false === strpos($buffer, 'Your host is trying to (re)connect too fast -- throttled')) {
                    foreach($bot["Channels"] as $key => $channel) {
                       sendData("JOIN", $channel);
                       $join_at_start = false;
                    }
                }
            }
        ?>
    </body>
</html>
