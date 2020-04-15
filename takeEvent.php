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

require_login($course, true, $cm);

/**
 * TakeEvent, modifie l'evenement dans la bdd, on créer une stdClass avec les champs qu'on veut modifier et on update
 * On recupere l'evenement pour verifier s'il y a un étudiant ou non pour faire la différence entre le passage de l'état 1 à 2 et de 2 a 3
 * 1 à 2 : null -> etu
 * 2 à 3 : etu          donc on change rien
 */
if(isset($_POST["idEvent"])){
    $etu = null;
    $arrayEvent = $DB->get_records("univtutoring_events",array('id' => $_POST["idEvent"]));
    $event = array_shift($arrayEvent);
    // recupere etudiant dans levenement s'il existe car de base $event->etudiant = NULL si rien
    $etu = $event->etudiant;
    $newEvent = new stdClass();
    $newEvent->id = $_POST["idEvent"];
    $newEvent->state = $_POST["state"]+1; 
    // Si $etudiant est NULL alors on met l'étudiant dedans
    if(!isset($etu)){
        $newEvent->etudiant = $_POST["etudiant"];
    }
    /**
     * Envoie des mails en fonction de l'etat
     */
    if($newEvent->state == 2){ 
        univtutoring_send_mail($event->tuteur,$newEvent->etudiant,"Créneau en attente", "Votre rendez-vous de tutorat le ".$event->start." a bien été pris en compte . Il est en attente de confirmation :",$id); 
        univtutoring_send_mail($newEvent->etudiant,$event->tuteur,"Créneau en attente", "Vous avez un créneau de confirmation",$id); 
    }elseif($newEvent->state == 3){
        univtutoring_send_mail($event->tuteur,$event->etudiant,"Créneau validé", "Votre rendez-vous de tutorat le ".$event->start." a bien été CONFIRME",$id); 
        univtutoring_send_mail($event->etudiant,$event->tuteur,"Créneau validé", "Vous avez bien validé le créneau",$id); 
    }
    $DB->update_record('univtutoring_events',$newEvent);
}