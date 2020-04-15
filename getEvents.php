<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of univtutoring
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_univtutoring
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace univtutoring with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... univtutoring instance ID - it should be named as the first character of the module.
if ($id) {
    $cm         = get_coursemodule_from_id('univtutoring', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $univtutoring  = $DB->get_record('univtutoring', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $univtutoring  = $DB->get_record('univtutoring', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $univtutoring->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('univtutoring', $univtutoring->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}
$context = context_module::instance($cm->id);
require_login($course, true, $cm);

// Permet de verifier de combien de jours est passé ou non un créneau

// Recupere la date d'aujourd'hui sours la forme 'DD-MM-YYYY
$fin = strtotime(date("d-m-Y"));

// Récupère les créneaux, un créneau = 1 tableau et tous dans 1 tableau pour bien être transformé en JSON et être lu par le calendrier
$events = $DB->get_records("univtutoring_events");
$eventJSON = array();
$name_tuteur = $name_etu = null;
foreach($events as $e){
    /**
     * Check si l'utilisateur à le droit, si cest soit le tuteur ou l'etudiant, verifie l'id du cours
     * De plus verifie l'etat car tout le monde peut voir l'état 1 (si pas d'étudiant et que state = 1)
     * Sinon dans l'état 2-3, check le user 
     */
    if(( (has_capability('mod/univtutoring:view_event',$context) && ($USER->id == $e->tuteur || $USER->id == $e->etudiant || ($e->etudiant == null && $e->state == 1))) && $e->course == $id ) || ( has_capability('mod/univtutoring:view_allevent',$context) && $e->course == $id ) ){
        $event = array();
        // Parti pour gérer le nom du créneau [Tuteur] course [Etudiant]
        $userDB = $DB->get_records("user",array('id' => $e->tuteur),'','lastname');
        $userVIEW = array_shift($userDB);
        if($userVIEW->lastname != null){
            $event["title"] = "[".$userVIEW->lastname."]";
        }
        $event["title"]= $event["title"]." ".$e->title;
        if($e->etudiant != null){
            $userDB = $DB->get_records("user",array('id' => $e->etudiant),'','lastname');
            $userVIEW = array_shift($userDB);
            if($userVIEW->lastname != null){
                $event["title"]= $event["title"]." [".$userVIEW->lastname."]";
            }
        }
        $event["id"] = $e->id;
        $event["course"]= $e->course;
        $event["start"]= $e->start;
        $event["end"]= $e->end;
        $event["state"] = $e->state;
        // Si levenement est passé est pas confirmé delete ou si il a plus de 1 ans delete (delete tous les x de l'année peut etre)
        $debut =strtotime(date("d-m-Y",strtotime($e->end)));
        if((ceil(($fin - $debut) / 86400) >= 0 && ($e->state == 2 || $e->state == 1)) || ($e->state == 3 && ceil(abs($fin - $debut) / 86400) > 365)){
            $DB->delete_records("univtutoring_events",array('id' => $e->id));
            continue;
        }
        $event["tuteur"] = $e->tuteur;
        $event["etudiant"] = $e->etudiant;
        // Gestion de la couleur de levent par rapport à l'état (VIEW)
        $event["borderColor"] = "black";
        if($e->state == 2){
            $event["backgroundColor"] = "orange";
        }else if($e->state == 3){
            $event["backgroundColor"] = "#00b549";
        }else if($e->state == 4){
            $event["backgroundColor"] = "#ffffff";
            $event["textColor"] = "#000000";
        }else if($USER->id == $e->tuteur && $e->state == 1){
            $event["backgroundColor"] ='#8292e3';
        }   
        array_push($eventJSON, $event);
    }
}
echo json_encode($eventJSON);