<?php
/**
 * @package    projects
 * @subpackage main
 * @author     
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: 
 */

function ps_workcosts($project, $projectlist = array(), $userlist = array())
{
    $data = ps_costs_get_data($project, $projectlist, $userlist);
    
    if (array_key_exists($project, $data)) {
	return (float) $data[$project]['workcosts'];
    }

    return 0.0;
}

function ps_specialcosts($project, $projectlist = array(), $userlist = array())
{
    $data = ps_costs_get_data($project, $projectlist, $userlist);
    
    if (array_key_exists($project, $data)) {
        return (float) $data[$project]['costs'];
    }

    return 0.0;
}

function ps_costs_daycount ($project, $projectlist = array(), $userlist = array())
{
    $data = ps_costs_get_data($project, $projectlist, $userlist);
    
    if (array_key_exists($project, $data)) {
        return (int) $data[$project]['daycount'];
    }

    return 0;   
}

function ps_costs_get_data ($project, $projectlist = array(), $userlist = array())
{
    static $project_costs = array();

    if (!array_key_exists($project, $project_costs)) {
        if (count($projectlist) == 0) {
            $projectlist = array ($project);
        }

        if ($projectlist[0] != 'gesamt') {
            $where = "WHERE p.ID IN (".implode(',',$projectlist).")";
        }
        
        $query = "SELECT p.ID, p.name, SUM(c.amount),
                         p.anfang, p.ende
                    FROM ".DB_PREFIX."projekte p
               LEFT JOIN ".DB_PREFIX."costs c
                      ON p.ID = c.projekt
                      $where
                     AND p.is_deleted is NULL
                GROUP BY p.ID";
        $result = db_query ($query) or db_die ();

        while ($row = db_fetch_row($result)) {
            list($ys,$ms,$ds)   = split('-',$row[3]);
            list($ye,$me,$de)   = split('-',$row[4]);
            $timestamp1         = mktime(0,0,0,$ms,$ds,$ys); 
            $timestamp2         = mktime(0,0,0,$me,$de,$ye); 
            $diff_seconds       = $timestamp2 - $timestamp1; 
            $days               = $diff_seconds / (60 * 60 * 24); 
            $days               = abs($days); 
            $days               = floor($days); 
            $project_costs[$row[0]] = array ('pid' => $row[0],
                                             'name' => $row[1],
                                             'costs' => $row[2],
                                             'daycount' => $days);
            
            if ($days > 0) {
                $project_costs[$row[0]]['perday'] = $row[2] / $days;
            }
        }
        
        $query = "SELECT tp.projekt, (tp.h+tp.m/60), u.hrate, p.stundensatz, u.vorname
                    FROM ".DB_PREFIX."timeproj tp,
                         ".DB_PREFIX."users u,
                         ".DB_PREFIX."projekte p
                   WHERE u.id = tp.users
                     AND p.id = tp.projekt
                     AND u.id IN(".implode(",", $userlist).")";
        
        $result = db_query ($query) or db_die ();

        while ($row = db_fetch_row($result)) {
            if (!empty($row[2])) {
                $project_costs[$row[0]]['workcosts']+= $row[1] * $row[2];
            } else {
                $project_costs[$row[0]]['workcosts']+= $row[1] * $row[3];
            }
        }
    }
    
    return $project_costs;
}
