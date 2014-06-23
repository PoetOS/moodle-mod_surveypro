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
 * Define all the backup steps that will be used by the backup_surveypro_activity_task
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete surveypro structure for backup, with file and id annotations
 */
class backup_surveypro_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        // root element describing surveypro instance
        $surveypro = new backup_nested_element('surveypro', array('id'), array(
                    'name', 'intro', 'introformat', 'newpageforchild',
                    'saveresume', 'captcha', 'history', 'anonymous',
                    'timeopen', 'timeclose', 'startyear', 'stopyear',
                    'maxentries', 'notifyrole', 'notifymore', 'thankshtml',
                    'thankshtmlformat', 'riskyeditdeadline', 'template',
                    'timecreated', 'timemodified'));

        $items = new backup_nested_element('items');

        $item = new backup_nested_element('item', array('id', 'type', 'plugin'), array(
                    'hidden', 'insearchform', 'advanced', 'sortindex', 'formpage',
                    'parentid', 'parentvalue', 'timecreated', 'timemodified'));

        $submissions = new backup_nested_element('submissions');

        $submission = new backup_nested_element('submission', array('id', 'userid'),
                    array('status', 'timecreated', 'timemodified'));

        $answers = new backup_nested_element('answers');

        $answer = new backup_nested_element('answer', array('id', 'itemid'), array(
                    'content', 'contentformat'));

        // Build the tree
        $surveypro->add_child($items);
        $items->add_child($item);

        // Apply for 'surveypro' subplugins stuff at item level
        $this->add_subplugin_structure('surveyprofield', $item, false);
        $this->add_subplugin_structure('surveyproformat', $item, false);

        // Apply for 'surveypro' subplugins stuff at answer level
        $this->add_subplugin_structure('surveyprofield', $answer, false);
        $this->add_subplugin_structure('surveyproformat', $answer, false);

        $surveypro->add_child($submissions);
        $submissions->add_child($submission);
        $submission->add_child($answers);
        $answers->add_child($answer);

        // Define sources
        $surveypro->set_source_table('surveypro', array('id' => backup::VAR_ACTIVITYID));

        $item->set_source_table('surveypro_item', array('surveyproid' => backup::VAR_ACTIVITYID));

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $submission->set_source_table('surveypro_submission', array('surveyproid' => backup::VAR_ACTIVITYID));
            $answer->set_source_table('surveypro_answer', array('submissionid' => '../../id'));
        }

        // Define id annotations
        $submission->annotate_ids('user', 'userid');

        // Define file annotations
        $surveypro->annotate_files('mod_surveypro', 'intro', null); // This file area does not have an itemid
        $surveypro->annotate_files('mod_surveypro', 'userstyle', null); // This file area does not have an itemid
        $surveypro->annotate_files('mod_surveypro', 'templatefilearea', null); // This file area does not have an itemid
        $surveypro->annotate_files('mod_surveypro', 'thankshtml', null); // This file area does not have an itemid
        $item->annotate_files('mod_surveypro', 'itemcontent', 'id'); // By itemid
        // $fileupload->annotate_files

        // Return the root element (surveypro), wrapped into standard activity structure
        return $this->prepare_activity_structure($surveypro);
    }
}