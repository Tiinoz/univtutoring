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

?>

<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
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
var date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
document.addEventListener('DOMContentLoaded', function() {
    var initialLocaleCode = 'fr';
    var localeSelectorEl = document.getElementById('locale-selector');
    var calendarEl = document.getElementById('calendar');
    var calendar;
    var test = "./getEvents.php?id=<?php echo $cm->id;?>";
    calendar = new FullCalendar.Calendar(calendarEl, {
    plugins: ['interaction', 'dayGrid', 'timeGrid', 'list', 'rrule' ],
    header: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
    },
    defaultDate: today,
    locale: initialLocaleCode,
    weekNumbers: true,
    navLinks: true, // can click day/week names to navigate views
    selectable: true,
    selectMirror: true,
    select: function(arg) {
      var id = prompt("id");
          id = parseInt(id);
          var title = prompt("Title of event");
          if(title){
            var start = "2020-02-24 12:00:00";
            var end = "2020-02-24 18:00:00";
            $.ajax({
              url: "./addEvents.php",
              type:"POST",
              data:{id:id, title:title, start:start, end:end},
              success:function(){
                calendar.refetchEvents();
                alert("Added succes");
              }
            })
          }
        calendar.unselect()
    },
      // editable: true,
      eventLimit: true, // allow "more" link when too many events
      events: test,
      
      eventClick: function(arg) {
        if (confirm('delete event?')) {
            arg.event.remove()
        }
    }
    });
    calendar.render();

  });


</script>


<?php
/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('univtutoring-'.$somevar);
 */
// $lll = $PAGE->get_renderer('mod_univtutoring');
// Output starts here.
// $test = new index_page($univtutoring,'parapagraphe');

// echo $lll->render($test);
// Conditions to show the intro can change to look for own settings or whatever.
if ($univtutoring->intro) {
    echo $OUTPUT->box(format_module_intro('univtutoring', $univtutoring, $cm->id), 'generalbox mod_introbox', 'univtutoringintro');
}

// Replace the following lines with you own code.
$id = optional_param('id', 0, PARAM_INT); // Course_module ID, o
   $cm         = get_coursemodule_from_id('univtutoring', $id, 0, false, MUST_EXIST);
echo $OUTPUT->heading('Yay! It works!');
// $rec     = $DB->get_records('events', array('id'=> $cm->instance));
$test = new stdClass();
$test->id = $USER->id;
$test->course = 5;
$test->title = 'TEST PROG';;
$test->start = '2020-02-19 12:00:00';
$test->end = '2020-02-19 18:00:00';
// $DB->insert_record('events',$test);

// $DB->execute("INSERT INTO events (id, course, title, start,end) VALUES ('$USER->id','PROG','Enzo PROG','2020-02-19 12:00:00','2020-02-19 18:00:00')");
// echo $rec2->title;
// $DB->delete_records("events");
$rec2     = $DB->get_records("events");

var_dump($rec2);
?>
  <div id='top'>
    <div class='left'>
    

    </div>
    <div class='right'>
      <span class='credits' data-credit-id='bootstrap-standard' style='display:none'>
        <a href='https://getbootstrap.com/docs/3.3/' target='_blank'>Theme by Bootstrap</a>
      </span>
      <span class='credits' data-credit-id='bootstrap-custom' style='display:none'>
        <a href='https://bootswatch.com/' target='_blank'>Theme by Bootswatch</a>
      </span>
    </div>

    <div class='clear'></div>
  </div>

  <div id='calendar'></div>

<?php

// Finish the page.
echo $OUTPUT->footer();


?>

