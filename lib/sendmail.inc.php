<?php

// $Id: sendmail.inc.php,v 1.10 2006/11/07 00:28:28 gustavo Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

//  several methodes to send mail
class send_mail
{

    var $mode;
    var $header_linebreak;
    var $body_linebreak;
    var $win_srv;
    var $auth;
    var $loc_host;
    var $smtp_host;
    var $smtp_acc;
    var $smtp_pass;
    var $pop_host;
    var $pop_acc;
    var $pop_pass;

    function send_mail($m=0, $mail_eoh="\r\n", $mail_eol="\r\n", $a=0, $lh='', $sh='', $sa='', $sp='', $ph='', $pa='', $pp='') {

        if ($a == 2 and $m < 1) die("<b>Error:  SMTP authentication needs socket!</b>");
        if ($m ==1) {
            if ($lh == '') die("<b>Error: localhost name needed!</b>");
            if ($sh == '') die("<b>Error: SMTP hostname needed!</b>");
            if ($a == 2 and ($sa == '' or $sp == '')) die("<b>Error: SMTP username and password needed!</b>");
        }
        if ($a == 1 and ($ph == '' or $pa == '' or $pp == '')) die("<b>Error: POP hostname, username and password needed!</b>");
        $this->mode = $m;
        $this->header_linebreak = $mail_eoh;
        $this->body_linebreak = $mail_eol;
        $this->auth = $a;
        $this->loc_host = $lh;
        $this->smtp_host = $sh;
        $this->smtp_acc = $sa;
        $this->smtp_pass = $sp;
        $this->pop_host = $ph;
        $this->pop_acc = $pa;
        $this->pop_pass = $pp;
        $this->win_srv = ($_SERVER['WINDIR'] or $_SERVER['windir']);
    }

    function go($to='', $subj='', $body='', $from='', $head='', $cc='',$bcc='', $arg='') {
        // $to, $cc, $bcc: recipient mail addresses, single or comma separated lists
        // $subj: subjekt
        // $from: senders mail adress
        // $body: plain text or multipart message, both ready to send sliced into lines
        // $head: additional headers (may contain a multipart message too, if $body is empty)
        // $arg: flag to put envelope-from to sendmail
        $lb = $this->header_linebreak;
        // enforce preset line delimiter
        $body = preg_replace("/\\r\\n|\\n|\\r/i",$this->body_linebreak,$body);
        $head = preg_replace("/\\r\\n|\\n|\\r/i",$lb,$head);
        //be sure to send a Reply-To header
        if (!stristr($head, 'Reply-To:')) $head = "Reply-To:".$from.$lb.$head;

        // build the envelop to
        $env_to = $to;
        if ($cc)  $env_to .= ','.$cc;
        if ($bcc) $env_to .= ','.$bcc;

        if ($this->auth ==1) {
            $mbox = imap_open ("{".$this->pop_host."/pop3:110}INBOX",$this->pop_acc,$this->pop_pass);
            if (!$mbox) die("<b>Error during POP before SMTP authentication<br />Online?<br />Proper POP account data?</b>");
            imap_close($mbox);
        }
        switch ($this->mode) {
            case 1: // performing SMTP via socket
                $s_head = "To:".$to.$lb;
                if($cc) $s_head .= "Cc:".$cc.$lb;
                $s_head .= $head;
                return $this->sock_mail($env_to, $subj, $body, $s_head, $from);
                break;
            case 2: // performing SMTP via socket - debug-output
                $s_head = "To:".$to.$lb;
                if($cc) $s_head .= "Cc:".$cc.$lb;
                $s_head .= $head;
                return $this->debug_sock_mail($env_to, $subj, $body, $s_head, $from);
                break;
            // using PHP mail()
            default:
                // Different treatment for mail() depending on the servers OS
                // Keep in mind, that things can change - so mail() too and this code has to follow. Cave Bcc!
                if($this->win_srv){ // Windows server only
                    $s_head = 'To:'.$to.$lb;
                    if($cc) $s_head .= 'cc:'.$cc.$lb;   //Don't write "Cc:" - else mail() will fail! Hope, that the client can read it well.
                }
                else { // others
                    $s_head = '';
                    if ($cc)  $s_head .= 'Cc:'.$cc.$lb;
                    if ($bcc) $s_head .= 'Bcc:'.$bcc.$lb;
                    if ($arg) $s_from = '-f'.$from;
                    else $s_from = '';
                    $env_to = $to;
                }
                // common
                $s_head .= 'From:'.$from.$lb.$head;
                if ($arg) {
                    return mail($env_to, $subj, $body, $s_head,$s_from);
                }
                else {
                    //echo"$env_to, $subj,$body,$s_head";
                    return mail($env_to, $subj,$body,$s_head);}
                break;
        }
    }

    function sock_mail($to, $subj, $body, $head, $from) {
        $lb = $this->header_linebreak;
        $adr = explode(',', $to);
        $hdr = explode($lb, $head);
        if ($body) {
            $bdy = preg_replace("/^\./","..",explode($this->body_linebreak,$body));
        }
        // build the array for the SMTP dialog. Line content is array(command, success code, additonal error message)
        if ($this->auth == 2) {// SMTP authentication methode AUTH LOGIN, use extended HELO "EHLO"
            $smtp = array(
                // call the server and tell the name of your local host
                array("EHLO ".$this->loc_host.$lb,"220,250","HELO error: "),
                // request to auth
                array("AUTH LOGIN".$lb,"334","AUTH error:"),
                // username
                array(base64_encode($this->smtp_acc).$lb,"334","AUTH error:"),
                // password
                array(base64_encode($this->smtp_pass).$lb,"235","AUTH error:"));
        }
        else {// no authentication, use standard HELO
            $smtp = array(
                // call the server and tell the name of your local host
                array("HELO ".$this->loc_host.$lb,"220,250","HELO error: "));
        }
        // envelop
        $smtp[] = array("MAIL FROM: <".$from.">".$lb,"250","MAIL FROM error: ");
        foreach ($adr as $a) {
            $smtp[] = array("RCPT TO: <".$a.">".$lb,"250","RCPT TO error: ");
        }
        // begin data
        $smtp[] = array("DATA".$lb,"354","DATA error: ");
        // header
        $smtp[] = array("Subject: ".$subj.$lb, '', '');
        $smtp[] = array("From:".$from.$lb,"","");
        foreach($hdr as $h) {$smtp[] = array($h.$lb, '', '');}
        // end header, begin the body
        $smtp[] = array($lb, '', '');
        if ($bdy) {
            foreach($bdy as $b) {
                $smtp[] = array($b.$this->body_linebreak, '', '');
            }
        }
        // end of message
        $smtp[] = array('.'.$lb,"250","DATA(end)error: ");
        $smtp[] = array("QUIT".$lb,"221","QUIT error: ");

        // open socket
        $fp = fsockopen($this->smtp_host, 25);
        if (!$fp)die("<b>Error:</b> No connect to ".$this->smtp_host);
        $banner = fgets($fp, 1024);
            // perform the SMTP dialog with all lines of the list
        foreach ($smtp as $req) {
            $r = $req[0];
            // send request
            fputs($fp, $req[0]);
            // get available server messages and stop on errors
            if ($req[1]) {
                while ($result = fgets($fp, 1024)){
                    if (substr($result, 3, 1) == ' ') break;
                };
                if (!strstr($req[1],substr($result,0,3)))die($req[2].$result);
            }
        }
        $result = fgets($fp, 1024);
        // close socket
        fclose($fp);
        return 1;
    }

    function debug_sock_mail($to, $subj, $body, $head, $from) {
        $lb = $this->header_linebreak;
        $adr = explode(',', $to);
        $hdr = explode($lb, $head);
        if ($body) {
            $bdy = preg_replace("/^\./","..",explode($this->body_linebreak,$body));
        }
        // build the array for the SMTP dialog. Line content is array(command, success code, additonal error message)
        if ($this->auth == 2) { // SMTP authentication methode AUTH LOGIN, use extended HELO "EHLO"
            $smtp = array(
                // call the server and tell the name of your local host
                array("EHLO ".$this->loc_host.$lb,"220,250","HELO error: "),
                // request to auth
                array("AUTH LOGIN".$lb,"334","AUTH error:"),
                // username
                array(base64_encode($this->smtp_acc).$lb,"334","AUTH error:"),
                // password
                array(base64_encode($this->smtp_pass).$lb,"235","AUTH error:"));
}
        else { // no authentication, use standard HELO
            $smtp = array(
                // call the server and tell the name of your local host
                array("HELO ".$this->loc_host.$lb,"220,250","HELO error: "));
        }
        // envelop
        $smtp[] = array("MAIL FROM: <".$from.">".$lb,"250","MAIL FROM error: ");
        foreach($adr as $a) {
            $smtp[] = array("RCPT TO: <".$a.">".$lb,"250","RCPT TO error: ");
        }
        // begin data
        $smtp[] = array("DATA".$lb,"354","DATA error: ");
        // header
        $smtp[] = array("Subject: ".$subj.$lb, '', '');
        $smtp[] = array("From:".$from.$lb, '', '');
        foreach($hdr as $h) {
            $smtp[] = array($h.$lb, '', '');
        }
        // end header, begin the body
        $smtp[] = array($lb, '', '');
        if ($bdy) {
            foreach($bdy as $b) {
                $smtp[] = array($b.$this->body_linebreak, '', '');
            }
        }
        // end of message
        $smtp[] = array('.'.$lb,'250',"DATA(end)error: ");
        $smtp[] = array("QUIT".$lb,"221","QUIT error: ");

        // open socket
        echo"Connecting to:&nbsp;&nbsp;".$this->smtp_host.":25<br />";
        $fp = fsockopen($this->smtp_host, 25);
        if (!$fp)die("<b>Error:</b> No connect to ".$this->smtp_host);
        $banner = fgets($fp, 1024);
        echo $banner."<br />";
            // perform the SMTP dialog with all lines of the list
        foreach ($smtp as $req) {
            $r = $req[0];
            // send request
            echo htmlspecialchars($req[0]).'<br />';
            fputs($fp, $req[0]);
            // get available server messages and stop on errors
            if ($req[1]) {
                while ($result = fgets($fp, 1024)) {
                    echo htmlspecialchars($result).'<br />';
                    if (substr($result,3,1) == ' ') break;
                }
                if (!strstr($req[1], substr($result, 0, 3))) die($req[2].$result);
            }
        }
        $result = fgets($fp, 1024);
        echo $result.'<br />';
        // close socket
        fclose($fp);
        return 1;
    }
}

?>
