<?php
/**
* calendar list view for several users
*
* @package    calendar
* @module     view
* @author     Albrecht Guenther, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: calendar_view_combi.php,v 1.19 2006/11/14 07:54:52 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

$user_params = calendar_get_user_params();
$user_params['act_param'] = '';

$tages_anfang = (!empty($settings['tagesanfang'])) ? $settings['tagesanfang'] : PHPR_DAY_START;
$tages_ende   = (!empty($settings['tagesende']))   ? $settings['tagesende']   : PHPR_DAY_END;

$daylen       = 60 * ($tages_ende - $tages_anfang);
$tinterval    = $dist;

echo '
<div class="inner_content">
';

switch ($mode) {
    case 5:
        $coloffset = 1;
        $wdoffset  = $wd_time;

        if ($tinterval == 0) $tinterval = 30;

        $tsanf    = mktime(0,0,0, $month, $day, $year);
        $danf     = date('Y-m-d', $tsanf);
        $nrofrows = (int) ceil($daylen / $tinterval);
        $sql_add  = " AND datum = '$danf' ORDER BY datum, anfang";

        echo calendar_view_prevnext_header('d', $user_params, true);

        $htmtab[0][0] = '&nbsp;';
        $base = mktime($tages_anfang,0,0, $month, $day, $year);

        for ($a=0; $a<$nrofrows; $a++) {
            if ($axis == 'x') {
                if (date('i', $base + $a*$tinterval * 60) == 0) {
                    $htmtab[1+$a][0] = date('H', $base + $a*$tinterval * 60);
                }
                else {
                    $htmtab[1+$a][0] = '&nbsp;-&nbsp;';
                }
            }
            else {
                $date_help_var = '&nbsp;'.date('H:i', $base + $a*$tinterval * 60).'&nbsp;';
                $timelink = $date_help_var;
                if (($a+1) == $nrofrows) {
                    $date_help_var = '&nbsp;'.$tages_ende.':00&nbsp;';
                    $timelink .= '<br />'.$date_help_var;
                }
                $htmtab[1+$a][0] = $timelink;
            }
            $isset[1+$a][0]  = 0;
            $repeat[1+$a][0] = 0;
        }
        break;

    case 6:
    case 7:
        $coloffset = 2;
        $wdoffset  = $wd_date + $wd_time;

        if ($tinterval == 0) $tinterval = (($axis=='x') ? 2 : 4)*60;

        $tsanf = mktime(0,0,0, $month, $day, $year);
        $d     = date('w', $tsanf);

        if ($d == 0) $d = 6;
        else         $d--;

        $tsanf -= (86400 *$d);
        $danf   = date('Y-m-d', $tsanf);
        $tsend  = $tsanf + 604800;
        $dend   = date('Y-m-d', $tsend); // add 7 days
        $nrofrows = 7 * (int)ceil($daylen / $tinterval);

        echo calendar_view_prevnext_header('w', $user_params, true);

        $htmtab[0][0] = '&nbsp;';
        $htmtab[0][1] = '&nbsp;';
        $sql_add = " AND datum >= '$danf' AND datum < '$dend' ORDER BY datum, anfang";
        $i = 1;
        $base = mktime($tages_anfang,0,0, $month, $day, $year);
        for ($d=0; $d<7; $d++) {
            $isrept = 0;
            $ts = $tsanf + $d * 86400;
            for ($a=0; $a<$nrofrows/7; $a++) {
                $name_day2_int = (date('w', $ts)-1 < 0) ? 6: date('w', $ts)-1;
                $date_help_var = $name_day2[$name_day2_int].'.&nbsp;'.$date_format_object->convert_db2user(date('Y-m-d', $ts));
                $args = '?mode=1&amp;view='.$view.'&amp;year='.date('Y', $ts).'&amp;month='.date('n', $ts).'&amp;day='.date('j', $ts).$user_params['act_param'].$sid;
                $htmtab[$i][0] = '&nbsp;<a href="./calendar.php'.$args.'" title="'.$date_help_var.'">'.$date_help_var.'</a>&nbsp;';
                if ($axis == 'x') {
                    if (date('i', $base + $a*$tinterval * 60 + $d * 86400) == 0) {
                        $htmtab[$i][1] = date('H', $base + $a*$tinterval * 60 + $d * 86400);
                    }
                    else {
                        $htmtab[$i][1] = '&nbsp;-&nbsp;';
                    }
                }
                else {
                    $htmtab[$i][1] = '&nbsp;'.date('H:i', $base + $a*$tinterval * 60 + $d * 86400).'&nbsp;';
                }
                $isset[$i][0]  = 0;
                $repeat[$i][0] = $isrept;
                $isset[$i][1]  = 0;
                $repeat[$i][1] = 0;
                $i++;
                $isrept = 1;
            }
        }
        break;

    case 8:
        $coloffset = 3;
        $wdoffset = $wd_week + $wd_date + $wd_time;

        if ($tinterval == 0) $tinterval = ($axis=='x') ? 4*60 : $daylen*60;

        $tsanf    = mktime(0,0,0, $month, 1, $year);
        $danf     = date('Y-m-d', $tsanf);
        $tsend    = mktime(0,0,0, $month+1, 1, $year);
        $dend     = date('Y-m-d', $tsend);
        $dayofmon = date('t', $tsanf);
        $nrofrows = $dayofmon * (int)ceil($daylen / $tinterval);

        echo calendar_view_prevnext_header('m', $user_params, true);

        $htmtab[0][0] = '&nbsp;';
        $htmtab[0][1] = '&nbsp;';
        $htmtab[0][2] = '&nbsp;';
        $sql_add = " AND datum >= '$danf' AND datum < '$dend' ORDER BY datum, anfang";
        $i = 1;
        $lastweek = -99;
        $base = mktime($tages_anfang,0,0, $month, 1, $year);

        for ($d=0; $d<$dayofmon; $d++) {
            $isrept = 0;
            $ts = $base + $d * 86400;
            $week = calendar_get_week_nr($d+1);
            $startweek = date('w', $ts);
            $startweek = date('d', $ts) - ($startweek ? $startweek : 7) + 1;
            if ($startweek <= 0) {
                $startweek -= 1;     // be compatible with calendar_control.php!
            }
            for ($a=0; $a<$nrofrows/$dayofmon; $a++) {
                $args = '?mode=2&amp;view='.$view.'&amp;year='.date('Y', $ts).'&amp;month='.date('n', $ts).'&amp;day='.date('j', $ts).$user_params['act_param'].$sid;
                $htmtab[$i][0] = '&nbsp;<a href="./calendar.php'.$args.'" title="'.$week.'. '.__('calendar week').'">'.$week.'</a>&nbsp;';
                $date_help_var = $date_format_object->convert_db2user(date('Y-m-d', $ts));
                $args = '?mode=1&amp;view='.$view.'&amp;year='.date('Y', $ts).'&amp;month='.date('n', $ts).'&amp;day='.date('j', $ts).$user_params['act_param'].$sid;
                $htmtab[$i][1] = '&nbsp;<a href="./calendar.php'.$args.'" title="'.$date_help_var.'">'.$date_help_var.'</a>&nbsp;';
                if ($axis == 'x') {
                    if (date('i', $base + $a*$tinterval * 60 + $d * 86400) == 0) {
                        $htmtab[$i][2] = date('H', $base + $a*$tinterval * 60 + $d * 86400);
                    }
                    else {
                        $htmtab[$i][2] = '&nbsp;-&nbsp;';
                    }
                }
                else {
                    $htmtab[$i][2] = '&nbsp;'.date('H:i', $base + $a*$tinterval * 60 + $d * 86400).'&nbsp;';
                }
                $isset[$i][0]  = 0;
                $repeat[$i][0] = $startweek == $lastweek ? 1 : 0;
                $isset[$i][1]  = 0;
                $repeat[$i][1] = $isrept;
                $isset[$i][2]  = 0;
                $repeat[$i][2] = 0;
                $i++;
                $lastweek = $startweek;
                $isrept = 1;
            }
        }
        break;
}

$a = 0;

// initialize PHP-array column-headers
$res = calendar_view_combi_get_users($combisel);
foreach ($res as $row) {
    $colcmp[$a] = $row['ID'];
    $devent[$a] = '';
    switch (PHPR_GROUPVIEWUSERHEADER) {
        case 2:  // loginname
            $n = $row['loginname'];
            break;
        case 1:  // shortname
            $n = $row['kurz'];
            break;
        case 0:  // name
        default: // default
            $n = $row['nachname'].' '.substr($row['vorname'], 0, 1).'.';
    }
    $htm = $n;
    $htmtab[0][$coloffset+$a] = '&nbsp;'.$htm.'&nbsp;';
    $isset[0][$coloffset+$a]  = 0;
    $repeat[0][$coloffset+$a] = 0;
    $a++;
}

$nrofcols = $a;
calendar_calc_wd($wdoffset, $nrofcols);

// initialize PHP-array "inside"-fields
for ($i=0; $i<$nrofrows; $i++) {
    for ($a=0; $a<$nrofcols; $a++) {
        $htmtab[1+$i][$coloffset+$a] = '&nbsp;';
        $isset[1+$i][$coloffset+$a]  = 0;
        $repeat[1+$i][$coloffset+$a] = 1;
    }
}


// get the (probably filtered) events of the combi selection
$res = calendar_view_combi_get_events($combisel, $sql_add);
foreach ($res as $row) {
    // calculate the row in table
    $ary = explode('-', $row['datum']);
    $daydiff = (mktime(0,0,0, $ary[1], $ary[2], $ary[0]) - $tsanf) / 86400;

    $ha = substr($row['anfang'], 0, 2);
    $ma = substr($row['anfang'], 2, 2);
    $he = substr($row['ende'], 0, 2);
    $me = substr($row['ende'], 2, 2);
    if ($ha < $tages_anfang) {
        $ha = 0;
        $ma = 0;
    }
    else {
        $ha -= $tages_anfang;
    }
    if ($he < $tages_anfang) {
        $he = $tages_anfang;
        $me = 0;
    }
    if ($he == $tages_ende) {
        $me = 0;
    }
    if ($he > $tages_ende) {
        $he = $tages_ende;
        $me = 0;
    }
    $he -= $tages_anfang;

    $i1 = $daydiff * (int) ceil($daylen/$tinterval) + (int) floor(($ha*60+$ma)/$tinterval);   // $i1: first row
    $i2 = $daydiff * (int) ceil($daylen/$tinterval) + (int) floor(($he*60+$me-1)/$tinterval); // $i2: last row

    // search the column in table
    for ($a=0; $a<$nrofcols; $a++) {
        // $a is the number of dest-column
        if ($colcmp[$a] == $row['an']) {
            break;
        }
    }
    // get the value for the field(s)
    $t = $row['event'];

    if ($cut and strlen($t) > $t_len) {
        $addinfo = true;
        $tiptext = $t;
        $text = html_out(substr($t, 0, $t_len - 1));
    }
    else {
        $addinfo = false;
        $tiptext = '';
        $text = html_out($t);
    }
    if ($addinfo) {
        if ($row['von'] != $row['an']) {
            $tiptext .= " \n".__('Created by').': '.
                        str_replace(',', ', ', slookup('users', 'nachname,vorname', 'ID', $row['von'],'1'));
        }
        $text .= '<img src="'.IMG_PATH.'/dots.gif" align="bottom" alt="'.$tiptext.'" title="'.$tiptext.'" border="0" />'."\n";
    }

    $alt_title = calendar_get_alt_title_tag($row);
/*
    // add link to edit event
    if ( calendar_can_read_events($user_params['user_id'], $row['visi']) ||
         calendar_can_edit_events($user_params['user_id'], $row['visi']) ) {
        $text = '<a href="./calendar.php?ID='.$row['ID'].'&amp;mode=forms&amp;view='.$view.$user_params['act_param'].$sid.
                '" title="'.$alt_title.'">'.$text."</a><br />\n";
    }
*/
    $t = $row['anfang'].' - '.$row['ende'].": \n$t";
    if ($row['anfang'] == '----' and $row['ende'] == '----') {
        $d = $row['datum'];
        if ($mode == 5) {
            // weekly view
            if ($devent[$a] == '') $devent[$a]  = "$text";
            else                   $devent[$a] .= "<br />$text";
        }
        else {
            if ($devent[$a] == '') $devent[$a]  = "$d $t";
            else                   $devent[$a] .= "<br />$d $t";
        }
    }
    if (!$text) {
        $text = '&nbsp;';
    }

    // the first row cannot be a repetition
    $repeat[1+$i1][$coloffset+$a] = 0;

    // set the value and flag
    for ($i=$i1; $i<=$i2; $i++) {
        if ($axis == 'x') {
            if ($isset[1+$i][$coloffset+$a]) {
                $htmtab[1+$i][$coloffset+$a] .= "$t";
            }
            else {
                $htmtab[1+$i][$coloffset+$a] = "$t";
            }
        }
        else {
            if ($isset[1+$i][$coloffset+$a]) {
                $htmtab[1+$i][$coloffset+$a] .= '<br />'.$text;
            }
            else {
                $htmtab[1+$i][$coloffset+$a] = $text;
            }
        }
        $isset[1+$i][$coloffset+$a] = ($row['status'] == '1') ? 5 : $row['partstat'] + 1;
    }

    // the row below the last row cannot be a repetition
    if ($i < $nrofrows) {
        $repeat[1+$i][$coloffset+$a] = 0;
    }
}


// reset repeat, if isset == 0
for ($i=0; $i<$nrofrows; $i++) {
    for ($a=0; $a<$nrofcols; $a++) {
        if ($isset[1+$i][$coloffset+$a] == 0) $repeat[1+$i][$coloffset+$a] = 0;
    }
}


echo '
<table cellspacing="1" cellpadding="0" class="calendar_table" width="100%" border="0">
';

if ($axis == 'h' or $axis == 'x') {
    for ($a=0; $a<$coloffset+$nrofcols; $a++) {
        echo "    <tr>\n";
        for ($i=0; $i<$nrofrows+1; $i++) {
            if ($repeat[$i][$a] == 0) {
                for ($r=1; $r<$nrofrows+1-$i and $repeat[$i+$r][$a]; $r++);
                $class = $cal_class[$isset[$i][$a]];

                if ($a < $coloffset) {
                    $class = 'calendar_day_prevnext';
                }

                if ($i >= 1 and $a >= $coloffset-1) $wd = ' width="80"';
                else                                $wd = '';

                if ($axis == 'x' and $i >= 1 and $a >= $coloffset) {
                    $w = 17+21*($r-1);
                    $alt_title = $htmrow[$a];
                    if ($htmtab[$i][$a] != '&nbsp;') {
                        $alt_title .= $htmtab[$i][$a];
                    }
                    echo '        <td'.$wd.' class="'.$class.'"'.($r>1 ? ' colspan="'.$r.'"' : '').">\n";
                    echo '            <span title="'.$alt_title.'">&nbsp;</span>'."\n";
                    echo "        </td>\n";
                }
                else {
                    if ($i == 0 and $a >= $coloffset and $devent[$a-$coloffset] != '') {
                        $devent2 = trim(ereg_replace("---- - ----:", '', $devent[$a-$coloffset]));
                        echo '        <td'.$wd.' class="'.$class.'"'.($r>1 ? ' colspan="'.$r.'"' : '').">\n";
                        echo '            <img src="'.IMG_PATH.'/b.gif" width="5" alt="'.$devent2.'" title="'.$devent2.'" align="top" />'.$htmtab[$i][$a]."\n";
                        echo "        </td>\n";
                    }
                    else {
                        echo '        <td'.$wd.' class="'.$class.'"'.($r>1 ? ' colspan="'.$r.'"' : '').">\n";
                        echo $htmtab[$i][$a]."\n";
                        echo "        </td>\n";
                    }
                }
            }
        }
        echo "    </tr>\n";
    }
}
else {
    for ($i=0; $i<$nrofrows+1; $i++) {
        echo "    <tr>\n";
        for ($a=0; $a<$coloffset+$nrofcols; $a++) {
            if ($repeat[$i][$a] == 0) {
                for ($r=1; $r<$nrofrows+1-$i and $repeat[$i+$r][$a]; $r++);
                $class = $cal_class[$isset[$i][$a]];

                if ($a < $coloffset) {
                    $wdth = 1;
                    $class = 'calendar_day_prevnext';
                }
                else {
                    $wdth = floor(100/$nrofcols);
                }

                if ($i == 0 and $a >= $coloffset and $devent[$a-$coloffset] != '') {
                    $devent2 = trim(ereg_replace("---- - ----:", '', $devent[$a-$coloffset]));
                    echo '        <td width="'.$wd.'" class="'.$class.'"'.($r>1 ? ' rowspan="'.$r.'"' : '').">\n";
                    echo $htmtab[$i][$a]."<br />\n".$devent2."\n";
                    echo "        </td>\n";
                }
                else {
                    echo '        <td valign="'.(($r > 1) ? 'middle' : 'top').'" width="'.$wdth.'%" class="'.$class.'"'.($r>1 ? ' rowspan="'.$r.'"' : '').">\n";
                    echo $htmtab[$i][$a]."\n";
                    echo "        </td>\n";
                }
            }
        }
        echo "    </tr>\n";
    }
}

echo '
</table>

<br /><br />

</div>
';


/**
 * get the (probably filtered) users of the selection.
 *
 * @param  array $combisel  the user parameters
 * @return array  the collected data of the users
 */
function calendar_view_combi_get_users($combisel) {
    $ret = array();
    $combisel[] = 0;
    $query = "SELECT ID, nachname, vorname, kurz, loginname
                FROM ".DB_PREFIX."users
               WHERE ID IN ('".implode("','", $combisel)."')
            ORDER BY ";

    switch (PHPR_GROUPVIEWUSERHEADER) {
        case 2:     // loginname
            $query .= 'loginname';
            break;
        case 1:     // shortname
            $query .= 'kurz';
            break;
        case 0:     // name
        default:    // default
            $query .= 'nachname, vorname';
    }

    $res = db_query($query) or db_die();
    while ($row = db_fetch_row($res)) {
        $ret[] = array( 'ID'        => $row[0]
                       ,'nachname'  => $row[1]
                       ,'vorname'   => $row[2]
                       ,'kurz'      => $row[3]
                       ,'loginname' => $row[4]
                      );
    }
    return $ret;
}

/**
 * get the (probably filtered) events of the selection.
 *
 * @param  array $combisel  the user parameters
 * @param  array $sql_add  additional sql options
 * @return array  the collected events
 */
function calendar_view_combi_get_events($combisel, $sql_add) {
    $ret = array();
    $combisel[] = 0;
    $query = "SELECT ID, von, an, event, anfang, ende, visi, partstat, datum, status
                FROM ".DB_PREFIX."termine
               WHERE an IN ('".implode("','", $combisel)."')
                     $sql_add";
    $res = db_query($query) or db_die();
    while ($row = db_fetch_row($res)) {
        $event = array( 'ID'       => $row[0]
                       ,'von'      => $row[1]
                       ,'an'       => $row[2]
                       ,'event'    => stripslashes($row[3])
                       ,'anfang'   => $row[4]
                       ,'ende'     => $row[5]
                       ,'visi'     => $row[6]
                       ,'partstat' => $row[7]
                       ,'datum'    => $row[8]
                       ,'status'   => $row[9]
                      );
        if (!calendar_can_see_events($event['an'], $event['visi'])) {
            continue;
        }
        $event['event'] = calendar_process_event_text( $event['an'],
                                                       $event['visi'],
                                                       $event['event'] );
        $ret[] = $event;
    }
    return $ret;
}

?>
