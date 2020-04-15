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
global $USER;
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

$event = \mod_univtutoring\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $univtutoring);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/univtutoring/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($univtutoring->name));
$PAGE->requires->css('/mod/univtutoring/styles.css');
echo $OUTPUT->header();
$context = context_module::instance($cm->id);
$langue = (isset($SESSION->lang)) ? $SESSION->lang : "fr";
?>

<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script src='js/fullcalendar-4.3.1/packages/core/main.js'></script>
<script src='js/fullcalendar-4.3.1/packages/interaction/main.js'></script>
<script src='js/fullcalendar-4.3.1/packages/bootstrap/main.js'></script>
<script src='js/fullcalendar-4.3.1/packages/daygrid/main.js'></script>
<script src='js/fullcalendar-4.3.1/packages/timegrid/main.js'></script>
<script src='js/fullcalendar-4.3.1/packages/core/locales-all.js'></script>
<script src='js/fullcalendar-4.3.1/packages/list/main.js'></script>
<script src='js/fullcalendar-4.3.1/packages/theme/theme-chooser.js'></script>
<script src='js/fullcalendar-4.3.1/packages/rrule/main.js'></script>
<script>
var today = new Date();
var calendar;
document.addEventListener('DOMContentLoaded', function() {
    var initialLocaleCode = "<?php echo $langue;?>";
    var localeSelectorEl = document.getElementById('locale-selector');
    var calendarEl = document.getElementById('calendar');
    var getEventsUrl = "./getEvents.php?id=<?php echo $cm->id;?>";
    var addEventsUrl = "./addEvents.php?id=<?php echo $cm->id;?>";
    var idCours =  <?php echo $cm->id;?>;
    calendar = new FullCalendar.Calendar(calendarEl, {
    plugins: ['interaction', 'dayGrid', 'timeGrid', 'list', 'rrule' ],
    header: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
    },
    defaultDate: today,
    default: true,
    locale: initialLocaleCode,
    weekNumbers: true,
    navLinks: true, // can click day/week names to navigate views

<?php if(has_capability('mod/univtutoring:add_event',$context)):?>
    selectable: true,
    selectMirror: true,
    select: function(arg) {
      // var id = prompt("id");
      // var course = prompt("cours");
      if(arg.start >= today){
          // Permet de séparer 1 créneau de 6h en 6 créneau de 1h
          var cpt = arg.end.getHours() - arg.start.getHours() // CPT = nb_creneau
          if(arg.end.getMinutes() != arg.start.getMinutes()){ // Si pas possible de bien séparer les créneaux on alert
            alert("<?php echo get_string('correcthours','univtutoring');?>");
          }
          else{
            // Pour chaque créneau 
            while (cpt > 0) {
              var title = prompt("<?php echo get_string('titleevent','univtutoring'); ?>");
              if (!title) {
                cpt = 0;
              }else{
                var tmp = arg.end.getHours();
                arg.end.setHours(arg.start.getHours() + 1);
                // Pour avoir l'affichage des heures comme souhaité
                var start = FullCalendar.formatDate(arg.start, {
                  month: "2-digit",
                  day: "2-digit",
                  year: "numeric",
                  hour: "2-digit",
                  minute: "2-digit",
                  seconde: "2-digit",
                  hour12: false,
                });
                var end = FullCalendar.formatDate(arg.end, {
                  month: "2-digit",
                  day: "2-digit",
                  year: "numeric",
                  hour: "2-digit",
                  minute: "2-digit",
                  seconde: "2-digit",
                  hour12: false,
                });
                start = moment(start).format('YYYY-MM-DD HH:mm:ss');
                end = moment(end).format('YYYY-MM-DD HH:mm:ss');
                // Ajout de l'evenement
                $.ajax({
                  type: 'POST',
                  url: './addEvents.php',
                  data: {id: idCours, title: title, start: start, end: end},
                  success: function () {
                    calendar.refetchEvents();
                    alert("<?php echo get_string('addevent','univtutoring'); ?>");
                  },
                  error: function () {
                    alert("Error");
                  },
                });
                arg.start.setHours(arg.start.getHours() + 1);
                cpt--;
              }
            }
          }
        }
      calendar.unselect();
    },
<?php endif?>
    eventLimit: true, // allow "more" link when too many events
<?php if(has_capability('mod/univtutoring:view_event',$context)):?>
    events: getEventsUrl,
<?php endif?>
    // Gere le trie par tuteur, reload les evenements quand on change
    eventRender: function eventRender( event, element, view ) {
      return ['all', event.event.extendedProps.tuteur].indexOf($('#tuteur_select').val()) >= 0
    },
    eventClick: function(arg) {
      calendar.refetchEvents();
      if(arg.event.end >= today){
        var id = arg.event.id;
        var state = arg.event.extendedProps.state;
        var idEtudiant = <?php echo $USER->id;?>;
        // Integrer les capabilities en JS compliqué du coup on check les ID utilisateurs
        // On envoi l'idCours par rapport au fait que sinon on mélange POST et GET comme ca pas de soucis
        // Prendre evenement donc on check l'état et si cest pas le tuteur lui même Verifie la confirmation aussi
        if( (arg.event.extendedProps.state == 1 && arg.event.extendedProps.tuteur != <?php echo $USER->id; ?> && confirm("<?php echo get_string('takeeventquest','mod_univtutoring');?>")) || (arg.event.extendedProps.state == 2 && arg.event.extendedProps.tuteur == <?php echo $USER->id; ?> && confirm("<?php echo get_string('confirmevent','mod_univtutoring');?>" )))
        {
          $.ajax({
            url:"takeEvent.php",
            type:"POST",
            data:{id:idCours, idEvent:id, state:state, etudiant:idEtudiant},
            success:function()
            {
              calendar.refetchEvents();
              alert("<?php echo get_string('takeevent','mod_univtutoring');?>");
            }
          })
        // Check si cest le tuteur et que levent est à l'état 1
        }else if((arg.event.extendedProps.state == 1 && arg.event.extendedProps.tuteur == <?php echo $USER->id; ?> && confirm("<?php echo get_string('deleteeventquest','mod_univtutoring');?>" ))){
          $.ajax({
            url:"deleteEvent.php",
            type:"POST",
            data:{id:idCours, idEvent:id, etudiant:idEtudiant},
            success:function()
            {
              calendar.refetchEvents();
              alert("<?php echo get_string('deleteevent','mod_univtutoring');?>");
            }
          })
        }else if(arg.event.extendedProps.state == 3 && arg.event.extendedProps.tuteur == <?php echo $USER->id; ?> && confirm("<?php echo get_string('verifeventquest','mod_univtutoring');?>" )){
          $.ajax({
            url:"verifEvent.php",
            type:"POST",
            data:{id:idCours, idEvent:id, etudiant:idEtudiant},
            success:function()
            {
              calendar.refetchEvents();
              alert("<?php echo get_string('verifevent','mod_univtutoring');?>");
            }
          })
        }else{
          // Check si c'est le tuteur et que l'état est à 2
          if(arg.event.extendedProps.state == 2 && arg.event.extendedProps.tuteur == <?php echo $USER->id; ?> && confirm("<?php echo get_string('declineeventquest','mod_univtutoring');?>")){
            $.ajax({
            url:"declineEvent.php",
            type:"POST",
            data:{id:idCours, idEvent:id, etudiant:idEtudiant},
            success:function()
            {
              calendar.refetchEvents();
              alert("<?php echo get_string('declineevent','mod_univtutoring');?>");
            }
          })
          }
          else if(arg.event.extendedProps.state == 3 && arg.event.extendedProps.tuteur == <?php echo $USER->id; ?> && confirm("<?php echo get_string('noverifeventquest','mod_univtutoring');?>")){
            $.ajax({
            url:"deleteEvent.php",
            type:"POST",
            data:{id:idCours, idEvent:id, etudiant:idEtudiant},
            success:function()
            {
              calendar.refetchEvents();
              alert("<?php echo get_string('deleteevent','mod_univtutoring');?>");
            }
          })
          }
        }
      }
    },
    });
    calendar.render();

  });

</script>


<?php

if ($univtutoring->intro) {
    echo $OUTPUT->box(format_module_intro('univtutoring', $univtutoring, $cm->id), 'generalbox mod_introbox', 'univtutoringintro');
}

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, o
$cm         = get_coursemodule_from_id('univtutoring', $id, 0, false, MUST_EXIST);
echo $OUTPUT->heading(get_string('header','mod_univtutoring'));

$context2 = context_course::instance($course->id);
$userroles = $DB->get_records('role_assignments', array('contextid' => $context2->id));
$username = $DB->get_records('user');

?>
  
  <div id='top'>
    <div class='left'>
        <?php echo get_string("filter","univtutoring")?>
        <select id="tuteur_select">
          <option value="all" selected>Tous</option>
          <?php
              // Pour chaque user tuteur on ajoute
              foreach ($userroles as $userrole){ 
                  if($userrole->roleid == 4){
          ?>
          <option value="<?php echo $userrole->userid; ?>"><?php echo $username[$userrole->userid]->lastname; ?></option>
          <?php }} ?>
        </select>
    </div>
    <div class='right'>
      <?php if(has_capability('mod/univtutoring:view_stats',$context)):?>
        <a href="./stats.php?id=<?php echo $id ?>">Statistiques</a>
      <?php endif ?>
      <span class='credits' data-credit-id='bootstrap-standard' style='display:none'>
        <a href='https://getbootstrap.com/docs/3.3/' target='_blank'>Theme by Bootstrap</a>
      </span>
      <span class='credits' data-credit-id='bootstrap-custom' style='display:none'>
        <a href='https://bootswatch.com/' target='_blank'>Theme by Bootswatch</a>
      </span>
    </div>
  </div>

  <div id='calendar'></div>

<!-- <footer>
  <div>
    Copyright
  </div>
</footer> -->

<?php

echo $OUTPUT->footer();

?>

<script>
$('#tuteur_select').on('change',function(){
      calendar.refetchEvents();
  });
</script>