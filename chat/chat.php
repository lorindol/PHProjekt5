<?php
/**
* single chat script
*
* @package    chat
* @module     main
* @author     Albrecht Guenther, Uwe Pries $Author: thorsten $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: chat.php,v 1.75.2.3 2007/02/26 14:14:34 thorsten Exp $
*/

define('PATH_PRE','../');
$module = 'chat';
require_once(PATH_PRE.'lib/lib.inc.php');
$_SESSION['common']['module'] = 'chat';


// check role
if (check_role('chat') < 1) die('You are not allowed to do this!');

// group mode? assign person to his group chat
$chat_group = $user_group;

chat_init();

/*
 *  main
 */
if(!isset($mode)) $mode = '';
switch ($mode) {
    case 'write':
        header("Content-type: text/html; charset=iso8859-1");
        chat_writetext();
        break;
    case 'alive':
        header("Content-type: text/html; charset=iso8859-1");
        chat_alive();
        break;
    case 'check':
        chat_check();
        break;
    case 'list':
        header("Content-type: text/html; charset=iso8859-1");
        echo chat_list();
        break;
    case 'write_accessibility_mode':
        header("Content-type: text/html; charset=iso8859-1");
        chat_writetext();
        /* fall-thru */
    default:
        if ($accessibility_mode) {
            header("Refresh: 30;url=chat.php");
        }
        echo set_page_header();
        require_once(PATH_PRE.'lib/navigation.inc.php');
        require_once(PATH_PRE.'lib/lib.inc.php');
        chat_main();
        break;
}


/*
 *  chat_input()
 *
 */
function chat_input() {
    global $chat_entry_type, $accessibility_mode;

    if (check_role('chat') <= 1) {
        echo check_role('chat');
        return;
    }
    if ($chat_entry_type == 'textfield') {
        $input = '<input type="text" id="content" name="content" size="70" value=""'.(!$accessibility_mode ? ' onkeyup="chat.onkeyup(event);"' : '').' />';
    } else {
        $input = '<textarea id="content" name="content" rows="3" cols="70">'.(isset($_POST['hidden_content']) ? xss($_POST['hidden_content']) : '').'</textarea>';
    }

    $input .= '
    <input type="hidden" name="mode" value="write_accessibility_mode" />
    <input type="submit" name="send" id="send" value="'.__('submit').'" '.(!$accessibility_mode ? ' onclick="chat.send_input();"' : '')." />&nbsp;&nbsp;&nbsp;\n";

    if (!$accessibility_mode) {
        $input .= "
    <br />
    <img src='smilies/smile.gif' alt=' :-)   ' onclick='chat.insert_smiley(\":-)\")' />
  | <img src='smilies/angry.gif' alt=' ):-(  ' onclick='chat.insert_smiley(\"):-(\")' />
  | <img src='smilies/upset.gif' alt=' :-()  ' onclick='chat.insert_smiley(\":-()\")' />
  | <img src='smilies/sad.gif' alt=' :-(     ' onclick='chat.insert_smiley(\":-(\")' />
  | <img src='smilies/blink.gif' alt=' ;-)   ' onclick='chat.insert_smiley(\";-)\")' />
  | <img src='smilies/ico_zig.gif' alt=' =~~ ' onclick='chat.insert_smiley(\"=~~\")' />
  | <img src='smilies/coffee.gif' alt=' U° ' onclick='chat.insert_smiley(\"U°\")' />
  | <img src='smilies/think_left.gif' alt=' .oO( ... ) ' onclick='chat.insert_think()' /><img src='smilies/think_right.gif' alt='' onclick='chat.insert_think()' />\n";
    }

    return $input;
}


/*
 *  chat_myurl_replace()
 *
 */
function chat_myurl_replace($str) {
    $pattern = '#(^|[^\"=]{1})(http://|https://|ftp://|mailto:|news:)([^\s<>]+)([\s\n<>]|$)#sm';
    $str = preg_replace($pattern,"\\1<a href=\"\\2\\3\" target='_blank'><u>\\2\\3</u></a>\\4",$str);
    $available = array('\:-\)' => 'smile',
                       '\)\:-\(' => 'angry',
                       '\:-\(\)' => 'upset',
                       '\:-\(' => 'sad',
                       '\;-\)' => 'blink',
                       'U°' => 'coffee',
                       '=~~' => 'ico_zig');
    foreach ($available as $input=>$output) {
        $str = ereg_replace($input, " <img src='smilies/$output.gif' alt=' ".$input." ' /> ", $str);
    }
    $str = preg_replace('°. o O \((.*)\)°'," <img src='smilies/think_left.gif' alt=' . o O (' /><span style=\"vertical-align:top;border-top:1px solid black;border-bottom:1px solid black;background:white;\"> \\1 </span><img src='smilies/think_right.gif' alt=') ' /> ", $str);

    return strtr($str, array("&lt;pre&gt;" => "<pre>", "&lt;/pre&gt;" => "</pre>"));
}


/*
 *  chat_writetext()
 *
 */
function chat_writetext() {
    global $user_ID, $user_name, $content, $chat_group;
    global $user_firstname, $user_loginname, $accessibility_mode;

    $content = htmlspecialchars($content);
    $content = ereg_replace("\r\n", "\r<br />\t\t\t\t", $content);

    $msg_to = '';
    $user_firstname2 = $user_firstname;
    if (isset($_SESSION['chat_setting']['userfirstname'])) {
        $user_firstname2 = $_SESSION['chat_setting']['userfirstname'];
    }

    if (eregi('^/nick([[:space:]]|$)', $content)) {
        $newnick = trim(substr($content,strpos($content,' ')+1));
        $newnick = substr(preg_replace("/[^a-zA-Z0-9\-_]/", '', $newnick),0,15);
        if (strlen($newnick) > 2) {
            $_SESSION['chat_setting']['userfirstname'] = $newnick;
            $content = sprintf("*** %s ändert den Nick auf %s", $user_firstname2, $newnick);
        } else {
            $content = "<b>$user_firstname2</b>: ".'*** Nick "'.$newnick.'" ist zu kurz';
            $input_was_private = true;
        }
        $input_was_me = true;

    } else if (eregi('^/oldnick', $content)) {
        $_SESSION['chat_setting']['userfirstname'] = $user_firstname;
        $content = sprintf("*** %s ändert seinen Nick wieder auf %s", $user_firstname2, $user_firstname);
        $input_was_me = true;

    } else if (eregi('^/help', $content)) {
        $content = "<b>$user_firstname2</b>: *** ".chat_show_help();
        $input_was_me = true;
        $input_was_private = true;

    } else if (eregi('^/cleanup', $content)) {
        $content = "<b>$user_firstname2</b>: *** ";
        // security check - only administrators can do this
        if ( $user_type!=3 || empty($chat_group)) {
            $content .= __('No access');
        } else {
            chat_cleanup_db();
            $content .= 'Chat-DB bereinigt.';
        }
        $input_was_me = true;
        $input_was_private = true;

    } else if (eregi('^/me([[:space:]]|$)', $content)) {
        $content = ereg_replace('^/me', "<b>$user_firstname2</b>", $content);
        $input_was_me = true;

    } else if (eregi('^/(msg|\.|,)([[:space:]]|$)', $content)) {
        if ($content{1} == '.' || $content{1} == ',') {
            $content = '/msg '.substr($content, 1);
        }
        $input_was_private = true;
        if (preg_match("/^\/msg\s([^\s]+)[\s\n]+(\S.*)$/is", $content, $data)) {
            if ($data[1] == ',' && isset($_SESSION['chat_setting']['msg']['comma'])) {
                $msg_to = $_SESSION['chat_setting']['msg']['comma'];
            } else if ($data[1] == '.' && isset($_SESSION['chat_setting']['msg']['dot'])) {
                $msg_to = $_SESSION['chat_setting']['msg']['dot'];
            } else if ($data[1] != ',' && $data[1] != '.') {
                $msg_to = strtolower($data[1]);
                $_SESSION['chat_setting']['msg']['dot'] = $msg_to;
            }
        }
        if ($msg_to != '') {
            $content = '<i><b>*'.$msg_to.'*</b></i> '.$data[2];
        } else {
            $content = "<b>$user_firstname2</b>: ".
                       '*** Letzte private Message ging an [.] "'.@$_SESSION['chat_setting']['msg']['dot'].
                       '" bzw. kam von [,] "'.@$_SESSION['chat_setting']['msg']['comma'].'"';
            $input_was_me = true;
        }

    } else if (eregi('^/att([[:space:]]|$)', $content)) {
        $attention = substr($content, strpos($content, ' ') + 1);
        switch ($attention) {
            case 'on':
                $_SESSION['chat_setting']['attention']['status'] = 'on';
                $content = 'Attention eingeschaltet.';
                break;
            case 'off':
                $_SESSION['chat_setting']['attention']['status'] = 'off';
                $content = 'Attention ausgeschaltet.';
                break;
            case 'css':
                $_SESSION['chat_setting']['attention']['mode'] = 'css';
                $content = 'Attention mode: nur css';
                break;
            case 'sound':
                $_SESSION['chat_setting']['attention']['mode'] = 'sound';
                $content = 'Attention mode: css & sound';
                break;
            case 'blue':
            case 'red':
            case 'green':
                $_SESSION['chat_setting']['attention']['color'] = $attention;
                $content = 'Attention Farbe: '.$attention;
                break;
            case 'alert':
                $_SESSION['chat_setting']['attention']['mode'] = 'alert';
                $content = 'Attention mode: css, sound & alert';
                break;
            case 'reset':
                chat_reset_attention();
            case '':
            case 'att':
            case 'info':
                $content = chat_show_att_info();
                break;
            default:
                if ($attention > 1 && $attention < 41) {
                    $_SESSION['chat_setting']['attention']['lines'] = (int) $attention;
                    $content = sprintf("Attention analysiert '%s' Zeilen.", $attention);
                } else if (substr($attention, 0, 4) == 'snd_' && is_readable('./sounds/'.substr($attention, 4).'.swf')) {
                    $_SESSION['chat_setting']['attention']['sound'] = $attention;
                    $content = sprintf("Neuer attention Sound: '%s'", $attention);
                } else {
                    $content = sprintf("unexplored attention syntax: '%s' ?!", $attention);
                }
        }
        $content = "<b>$user_firstname2</b>: *** ".$content;
        $input_was_me = true;
        $input_was_private = true;
    }

    if (trim($content) != '') {
        if ($input_was_me) {
            $zeile = '<td colspan="2" valign="top">'.date("H:i").' <i style="text-indent:-1cm;">___EINGABE___</i></td>';
        } else {
            //$span = '<b>'.$user_firstname2.'</b>:';
            $span = '<span title="MSG: '.$user_loginname.' | REAL: '.$user_firstname.' | NICK: '.$user_firstname2.'" style="cursor:pointer;"';
            if (!$accessibility_mode) {
                $span .= '
                    onMouseover="this.style.textDecoration=\'underline\'"
                    onMouseout="this.style.textDecoration=\'none\'"
                    onclick="chat.insert_firstname(\''.$user_firstname2.'\');"
                ';
            }
            $span .= '><b>'.$user_firstname2.'</b>:</span>';
            $zeile = '<td nowrap="nowrap" width="1%" valign="top">'.date("H:i").' '.$span.'</td><td>___EINGABE___</td>';
        }
        if ($input_was_private) {
            if ($msg_to == '') {
                $msg_to = $user_ID;
            } else {
                $msg_to = chat_get_user_id($msg_to);
            }
        } else {
            $msg_to = 0;
        }
        $query = "INSERT INTO ".DB_PREFIX."chat
                                  (     gruppe          ,          von,           an,          zeit, zeile, eingabe )
                           VALUES (".(int)$chat_group.", ".(int)$user_ID.", ".(int)$msg_to.", ".(int)time().",
                                         '".xss($zeile)."', '".xss($content)."')";
        $result = db_query($query) or db_die();
    }

    if (!$accessibility_mode) {
        echo chat_list();
    }
}


/*
 *  chat_main()
 */
function chat_main() {
    global $chat_direction, $accessibility_mode, $module;

    // calculate chat log refresh time
    if (defined('PHPR_CHAT_CONTENT_FREQ') && PHPR_CHAT_CONTENT_FREQ > 0) {

        // we will use the chat log (in second) to create the miliseconds refresh
        $chat_log_refresh_time = PHPR_CHAT_CONTENT_FREQ * 1000;

    }
    else {
        $chat_log_refresh_time = 5000; // default refresh value
    }

    chat_print_header();

    if (!$accessibility_mode) {
?>

<script type="text/javascript" type="text/javascript" src="chat.js?<?php echo md5(time()); ?>"></script>
<script type="text/javascript" type="text/javascript">
<!--
var chat;

function loadchat (event) {
    chat = new CHAT(document.getElementById('content'), document.getElementById('chatContent'), '<?php echo (isset($chat_direction) ? $chat_direction : 'top') ?>');
    setInterval(function() { chat.list(); }, <?php echo $chat_log_refresh_time; ?>);
    setInterval(function() { chat.alive(); }, 23000);

    var set_div_dimensions = function() {
        chat.output.style.width  = (window.innerWidth || document.body.offsetWidth) - 320 + "px";
        document.getElementById('chatUsers').style.height = chat.output.style.height = (window.innerHeight || document.documentElement.offsetHeight) - 200 + "px";

        if (chat.dir == 'bottom') {
            chat.output.scrollTop = 10000;
        }
    }

    dojo.event.connect(window, 'onresize', set_div_dimensions);
    set_div_dimensions();

    if (navigator.appName == 'Microsoft Internet Explorer') {
        document.body.style.behavior = 'url(#default#download)';
    }
}
dojo.addOnLoad(loadchat);
//-->
</script>
<?php
    } // if ($accessibility_mode) {
?>
<div id="global-header">
<?php
    echo get_tabs_area(array());
    echo breadcrumb($module);
?>
</div><div id="global-content">
        <form autocomplete="off"<?php echo (!$accessibility_mode ? ' onsubmit="return false;"' : ' action="'.$_SERVER['SCRIPT_NAME'].'" method="post"'); ?> name="frm">
<?php
    $lines =& chat_list();

    // here's start of the table
?>
            <div id="chatContent"><?php echo $lines; ?></div>
            <div id="chatUsers"><?php echo chat_alive(); ?></div>
            <br clear="all" />
            <div id="chatInput"><?php echo chat_input(); ?></div>
        </form>
    </div>
</div>
</body>
</html>
<?php
}


function chat_code_repl_cb($matches) {
    $geshi =& new GeSHi(strtr($matches[2], array("&quot;" => "\"", "&lt;" => "<", "&gt;" => ">")), $matches[1]);
    return $geshi->parse_code();
}


function chat_list() {
    global $user_ID, $chat_group, $chat_direction, $accessibility_mode;

    if (!defined('PHPR_CHAT_LAST_HOURS')) {
        define('PHPR_CHAT_LAST_HOURS', '24');
    }

    $zeit = time() - (PHPR_CHAT_LAST_HOURS * 60 * 60);
    $query = "SELECT id, zeile, eingabe, von, an
                FROM ".DB_PREFIX."chat
               WHERE gruppe = ".(int)$chat_group."
                 AND (von = ".(int)$user_ID." OR an = ".(int)$user_ID." OR an = 0)
                 AND zeit > ".(int)$zeit."
            ORDER BY zeit DESC, id";

    // read the result into an array - one line one element
    $result = db_query($query) or db_die();
    $output = "<table id='chatContent_table' border='0' cellpadding='1' cellspacing='0' style='width:97%;'>";
    $output_array = array();
    while ($row = db_fetch_row($result)) {
        $pattern = "/&lt;\?(.+?)[\n\t ](.+?)\ ?\?&gt;/is";
        // syntax highlighting, currently disabled
        // $row[2] = preg_replace_callback($pattern, "chat_code_repl_cb", $row[2]);

        $output_array[] = chat_myurl_replace(stripslashes('<tr from="' . $row[3] . '" to="' . $row[4] . '">'.str_replace('___EINGABE___', nl2br($row[2]), $row[1]).'</tr>'))."\n";
    }

    // add <h1> tags to last line
    if ($accessibility_mode) {
        $regs = array(
            "/(\d+:\d+\ )<span.*?>(.*?)<\/span>/s",
            "/<\/td><td>(.*?)<\/td><\/tr>/s",
        );

        $repl = array(
            "<h1>\\1 \\2</h1>",
            "</td><td><h1>\\1</h1></td></tr>",
        );

        $output_array[0] = preg_replace($regs, $repl, $output_array[0]);
    }

    // notify user via bell/sound/blink/earthquake...
    chat_user_attention($output_array, $object_str);

    if (isset($chat_direction) && $chat_direction == 'bottom' && !$accessibility_mode) {
        $output_array = array_reverse($output_array);
    }

    $output .= implode('', $output_array)."</table>\n".$object_str;
    return preg_replace("°\n°", "", $output);
}


/**
 *  chat_user_attention()
 */
function chat_user_attention(&$lines, &$object_str) {
    global $user_firstname, $user_ID;

    if (empty($_SESSION['chat_setting']['attention']['status'])) {
        chat_reset_attention();
    } else if ($_SESSION['chat_setting']['attention']['status']=='off') {
        return;
    }
    $lines_to_check = $_SESSION['chat_setting']['attention']['lines'];

    if (isset($_SESSION['chat_setting']['userfirstname'])) {
        $chat_userfirstname = strtolower($_SESSION['chat_setting']['userfirstname']);
    } else {
        $chat_userfirstname = strtolower($user_firstname);
    }
    $search_words = implode('|', array_merge(explode(' ', $chat_userfirstname), explode(' ', $user_firstname)));

    $found_line = array();
    foreach ($lines as $key => $line) {
        preg_match_all("/tr from=\"(\d+)\" to=\"(\d+)\"/", $line, $matches);
        $from = $matches[1][0];
        $to   = $matches[2][0];

        if ($from != $user_ID) {
            if ($to == $user_ID) {
                $found_line[] = $key;
            } else {
                if (preg_match("/\b(" . $search_words . ")\b/i", $line)) {
                    $found_line[] = $key;
                    //break;
                }
            }
        }
        $lines_to_check--;
        if ($lines_to_check < 1) {
            break;
        }
    }

    if (count($found_line) == 0) {
        return;
    }

    $color = array( 'blue'=>'#0099CC', 'red'=>'#CC3300', 'green'=>'#339933' );
    $last_line_md5 = md5($lines[$found_line[0]]);
    $bcol = chat_gradient(count($found_line), $color[$_SESSION['chat_setting']['attention']['color']], '#E0E0E0');
    for ($i=0; $i<count($found_line); $i++) {
        $col = ($i & 1) ? '#000' : '#fff';
        $lines[$found_line[$i]] = str_replace('<tr', '<tr style="font-weight:bold;color:'.$col.';background-color:'.$bcol[$i].';"', $lines[$found_line[$i]]);
    }

    if ( isset($_SESSION['chat_setting']['attention']['last_line_md5']) &&
         $_SESSION['chat_setting']['attention']['last_line_md5'] == $last_line_md5 ) {
        return;
    }
    $_SESSION['chat_setting']['attention']['last_line_md5'] = $last_line_md5;
    $alert = '';
    switch ($_SESSION['chat_setting']['attention']['mode']) {
        case 'alert':
            $alert = "\n".'  setTimeout("alert(\'PHProjekt Chat Notification Alert!\')", 1000);';
        case 'sound':
            $flash_loop = 'false';
            $flash_file = './sounds/'.substr($_SESSION['chat_setting']['attention']['sound'], 4).'.swf';
            if (is_readable($flash_file)) {
                $object_str = '
<div style="position:absolute;top:0px;left:0px;">
    <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="1" height="1">
        <param name="movie" value="'.$flash_file.'" />
        <param name="quality" value="high" />
        <param name="menu" value="false" />
        <param name="play" value="true" />
        <param name="loop" value="'.$flash_loop.'" />
        <embed src="'.$flash_file.'" width="1" height="1" quality="high" menu="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" play="true" loop="'.$flash_loop.'"></embed>
        <img src="../img/t.gif" width="1" height="1" alt="" />
    </object>
</div>
';
            }
            break;
    }
}


/*
 *  chat_reset_attention()
 *
 */
function chat_reset_attention() {
    $_SESSION['chat_setting']['attention']['status'] = 'on';
    $_SESSION['chat_setting']['attention']['mode']   = 'sound';
    $_SESSION['chat_setting']['attention']['lines']  = 20;
    $_SESSION['chat_setting']['attention']['color']  = 'blue';
    $_SESSION['chat_setting']['attention']['sound']  = 'snd_boing';
}


/*
 *  chat_alive()
 *
 */
function chat_alive() {
    global $chat_group, $chat_alive_freq, $user_name, $user_firstname, $user_loginname, $bgcolor2;
    global $accessibility_mode;

    if (isset($_SESSION['chat_setting']['userfirstname'])) {
        $chat_userfirstname = $_SESSION['chat_setting']['userfirstname'];
    } else {
        $chat_userfirstname = $user_firstname;
    }

    $time = time();
    $alive = array();
    $myself = false;

    $result = db_query("SELECT ID, user_name, user_loginname, chat_userfirstname, zeit
                          FROM ".DB_PREFIX."chat_alive
                         WHERE gruppe = ".(int)$chat_group."
                      ORDER BY user_name") or db_die();

    while ($row = db_fetch_row($result)) {
        if ($row[1] == ($user_firstname.' '.$user_name)) {
            // take the record into the new array with the actual time
            $result2 = db_query("UPDATE ".DB_PREFIX."chat_alive
                                    SET chat_userfirstname = '".addslashes($chat_userfirstname)."',
                                        zeit = ".(int)$time."
                                  WHERE ID = ".(int)$row[0]) or db_die();
            $alive[] = array($row[0], $row[1], $row[2], $chat_userfirstname, $time);
            $myself = true;
        } else if (($row[4] + $chat_alive_freq + 5) > $time) {
            // take actual records of other users into the new array
            $alive[] = $row;
        }
    }

    if (!$myself) {
        $result = db_query("INSERT INTO ".DB_PREFIX."chat_alive
                                        (user_name, user_loginname, chat_userfirstname, zeit, gruppe)
                                 VALUES ('".addslashes($user_firstname.' '.$user_name)."',
                                         '".addslashes($user_loginname)."','".addslashes($chat_userfirstname)."',
                                         ".(int)$time.",".(int)$chat_group.")") or db_die();
        $alive[] = array($dbIDnull, $user_firstname.' '.$user_name, $user_loginname, $chat_userfirstname, $time);
    }

    foreach ($alive as $oneAlive) {
        echo '<span title="MSG: '.$oneAlive[2].' | NICK: '.$oneAlive[3].'" style="cursor:pointer;"';
            if (!$accessibility_mode) {
                echo '
                    onMouseover="this.style.textDecoration=\'underline\'"
                    onMouseout="this.style.textDecoration=\'none\'"
                    onclick="chat.insert_msgname(\''.$oneAlive[2].'\');"
                    ';
            }
        echo '>'.$oneAlive[1].'</span><br />'."\n";
    }
}


/*
 *  chat_print_header()
 *
 */
function chat_print_header() {
    echo '<html>
<head>
<style type="text/css">body, td {font-family: Arial, Helvetica, sans-serif;font-size:12px;color:#000000;}</style>
</head>
';
}


/*
 *  chat_gradient()
 *
 */
function chat_gradient($steps, $color1, $color2) {
    $r1 = hexdec(substr($color1,1,2));
    $g1 = hexdec(substr($color1,3,2));
    $b1 = hexdec(substr($color1,5,2));

    $r2 = hexdec(substr($color2,1,2));
    $g2 = hexdec(substr($color2,3,2));
    $b2 = hexdec(substr($color2,5,2));

    $diff_r = $r2-$r1;
    $diff_g = $g2-$g1;
    $diff_b = $b2-$b1;

    $color = array();
    for ($i=0; $i<$steps; $i++) {
        $factor = $i / $steps;
        $r = round($r1 + $diff_r * $factor);
        $g = round($g1 + $diff_g * $factor);
        $b = round($b1 + $diff_b * $factor);
        $color[] = '#'.sprintf("%02X",$r).sprintf("%02X",$g).sprintf("%02X",$b);
    }
    return $color;
}


/*
 *  chat_sounds()
 *
 */
function chat_sounds() {
    $sounds = array();
    if (is_dir('./sounds/')) {
        $handle = opendir('./sounds/');
        while ($file = readdir($handle)) {
            if (substr($file, -4) == '.swf') {
                $sound = 'snd_'.basename($file, '.swf');
                if ($sound == $_SESSION['chat_setting']['attention']['sound']) {
                    $sound = '<b>'.$sound.'</b>';
                }
                $sounds['snd_'.basename($file, '.swf')] = $sound;
            }
        }
        closedir($handle);
    }
    ksort($sounds);
    return $sounds;
}


/*
 *  chat_get_user_id()
 *
 */
function chat_get_user_id($loginname) {
    $ret = -1;
    $loginname = trim($loginname);
    if ($loginname == '') {
        return $ret;
    }
    $result = db_query("SELECT ID
                          FROM ".DB_PREFIX."users
                         WHERE LCASE(loginname) = '".strtolower($loginname)."'") or db_die();

    $row = db_fetch_row($result);
    if ($row[0]) {
        $ret = $row[0];
    }
    return $ret;
}


/*
 *  chat_cleanup_db()
 *
 */
function chat_cleanup_db() {
    global $chat_group;

    // remove entries in db older than one week
    $zeit = time() - (60 * 60 * 24 * 7);

    $result = db_query("DELETE FROM ".DB_PREFIX."chat
                              WHERE gruppe = ".(int)$chat_group."
                                AND zeit < ".(int)$zeit) or db_die();
    $result = db_query("DELETE FROM ".DB_PREFIX."chat_alive
                              WHERE gruppe = ".(int)$chat_group."
                                AND zeit < ".(int)$zeit) or db_die();
}


/*
 *  chat_show_help()
 *
 */
function chat_show_help() {
    $help = "<b>Chat help</b>
<b>/att [info]</b> show current attention settings
<b>/cleanup</b> remove chat entries older than one week (admin only)
<b>/help</b> this help
<b>/me &lt;message&gt;</b> write an ego message
<b>/msg</b> show infos about last private message
<b>/msg &lt;user&gt; &lt;message&gt;</b> write a private message to user
<b>/nick &lt;new nick&gt;</b> set a new nick
<b>/oldnick</b> set back the original nick
\n";
    return $help;
}


/*
 *  chat_show_att_info()
 *
 */
function chat_show_att_info() {
    $status = '&lt;'.($_SESSION['chat_setting']['attention']['status']=='on'?'<b>on</b>|off':'on|<b>off</b>').'&gt;';
    $mode = array();
    foreach (array('css','sound','alert') as $aMode) {
        if ($aMode == $_SESSION['chat_setting']['attention']['mode']) {
            $aMode = '<b>'.$aMode.'</b>';
        }
        $mode[] = $aMode;
    }
    $mode = '&lt;'.implode('|', $mode).'&gt;';
    $sound = '&lt;'.implode('|', chat_sounds()).'&gt;';
    $color = array();
    foreach (array('blue','red','green') as $aColor) {
        if ($aColor == $_SESSION['chat_setting']['attention']['color']) {
            $aColor = '<b>'.$aColor.'</b>';
        }
        $color[] = $aColor;
    }
    $color = '&lt;'.implode('|', $color).'&gt;';
    $lines = '&lt;';
    if ($_SESSION['chat_setting']['attention']['lines'] == 2) {
        $lines .= '<b>2</b>-40';
    } else if ($_SESSION['chat_setting']['attention']['lines'] == 40) {
        $lines .= '2-<b>40</b>';
    } else {
        $lines .= '2-<b>'.$_SESSION['chat_setting']['attention']['lines'].'</b>-40';
    }
    $lines .= '&gt;';
    $att = "<b>Attention information</b>
<b>/att</b> ".$status." switch attention on/off
<b>/att</b> ".$mode." select attention mode
<b>/att</b> ".$sound." select sound for attention
<b>/att</b> ".$lines." number of lines to highlight
<b>/att</b> ".$color." color for highlighting
<b>/att reset</b> - reset all values to default
\n";
    return $att;
}


/**
 * initialize the chat stuff and make some security checks
 *
 * @return void
 */
function chat_init() {
    global $accessibility_mode;

    if (isset($_GET['accessibility_mode']) || $accessibility_mode == 1) {
        $_SESSION['accessibility_mode'] = true;
    }
    if (!isset($_SESSION['accessibility_mode']) || $accessibility_mode <> 1) {
        $_SESSION['accessibility_mode'] = false;
    }
    $accessibility_mode = $_SESSION['accessibility_mode'];

}

?>
