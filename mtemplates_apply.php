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

/*
 * Prints a particular instance of surveypro
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');
require_once($CFG->dirroot.'/mod/surveypro/classes/mtemplate.class.php');
require_once($CFG->dirroot.'/mod/surveypro/forms/mtemplates/apply_form.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$s = optional_param('s', 0, PARAM_INT);  // surveypro instance ID

if (!empty($id)) {
    $cm = get_coursemodule_from_id('surveypro', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $surveypro = $DB->get_record('surveypro', array('id' => $s), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $surveypro->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $course->id, false, MUST_EXIST);
}

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/surveypro:applymastertemplates', $context);

// -----------------------------
// calculations
// -----------------------------
$mtemplateman = new mod_surveypro_mastertemplate($surveypro, $context);

// -----------------------------
// define $applymtemplate return url
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('mtemplates_apply.php', $paramurl);
// end of: define $applymtemplate return url
// -----------------------------

// -----------------------------
// prepare params for the form
$formparams = new stdClass();
$formparams->cmid = $cm->id;
$formparams->surveypro = $surveypro;
$formparams->mtemplateman = $mtemplateman;
$formparams->inline = false;

$applymtemplate = new surveypro_applymtemplateform($formurl, $formparams);
// end of: prepare params for the form
// -----------------------------

// -----------------------------
// manage form submission
if ($applymtemplate->is_cancelled()) {
    $returnurl = new moodle_url('utemplates_add.php', $paramurl);
    redirect($returnurl);
}

if ($mtemplateman->formdata = $applymtemplate->get_data()) {
    $mtemplateman->apply_template();
}
// end of: manage form submission
// -----------------------------

// -----------------------------
// output starts here
// -----------------------------
$url = new moodle_url('/mod/surveypro/mtemplates_apply.php', array('s' => $surveypro->id));
$PAGE->set_url($url);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

// make bold the navigation menu/link that refers to me
navigation_node::override_active_url($url);

echo $OUTPUT->header();

$moduletab = SURVEYPRO_TABMTEMPLATES; // needed by tabs.php
$modulepage = SURVEYPRO_MTEMPLATES_APPLY; // needed by tabs.php
require_once($CFG->dirroot.'/mod/surveypro/tabs.php');

$mtemplateman->friendly_stop();

if (surveypro_count_submissions($surveypro->id, SURVEYPRO_STATUSALL)) {
    echo $OUTPUT->notification(get_string('hassubmissions_alert', 'surveypro'));
}

$message = get_string('applymtemplateinfo', 'surveypro');
echo $OUTPUT->box($message, 'generaltable generalbox boxaligncenter boxwidthnormal');

$applymtemplate->display();

// Finish the page
echo $OUTPUT->footer();