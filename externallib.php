<?php
global $CFG,$DB;
require_once ("$CFG->libdir/externallib.php");

class local_mipimanage_external extends external_api
{

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function add_instance_parameters()
    {
        return new external_function_parameters(array(
            'copy' => new external_single_structure(array(
                'source' => new external_value(PARAM_INT, 'Module id of master quiz', VALUE_REQUIRED),
                'newname' => new external_value(PARAM_TEXT, 'What to call the new one', VALUE_REQUIRED),
                'courseid' => new external_value(PARAM_INT, 'Course containing MIPI', VALUE_REQUIRED),
                'destsection' => new external_value(PARAM_INT, 'Section id for copy', VALUE_REQUIRED),
                'teacher' => new external_value(PARAM_TEXT, 'Teacher userid', VALUE_REQUIRED),
            ))
        ));
    }

    public static function add_instance($copy)
    {
        global $CFG, $DB;
        require_once ($CFG->dirroot . '/course/lib.php');
        require_once ($CFG->dirroot . '/lib/modinfolib.php');
        require_once ($CFG->dirroot . '/lib/accesslib.php');
        require_once ($CFG->dirroot . '/user/externallib.php');
        require_once ($CFG->dirroot . '/enrol/manual/externallib.php');
        require_once ($CFG->dirroot . '/group/externallib.php');
        require_once ($CFG->dirroot . '/group/lib.php');
        $course = $copy['courseid'];
        $source = $copy['source'];
        $newname = $copy['newname'];
        $newsec = $copy['destsection'];
        $teacher = $copy['teacher'];
        $modinfo = get_fast_modinfo($course);
        // Check courses
        $allmods = $modinfo->get_instances();

        foreach ($allmods as $thismod)
            foreach ($thismod as $onemod) {
                $modname = $onemod->modname;
                $instancename = $onemod->name;
                $thisid = $onemod->id;
                if ($modname == 'quiz' && $instancename == $newname)
                    $newmod = $thisid;
            }
        // Check whether group exists
        $groups = core_group_external::get_course_groups($course);
        // var_dump($groups);
        $groupfor = 0;
        foreach ($groups as $thisgroup) {
            if ($thisgroup['name'] == $newname) {
                $groupfor = $thisgroup['id'];
            }
        }
        // var_dump($groupfor);
        // If it doesn't...
        if ($groupfor == 0) {
            // Create Group
            $data = new stdClass();
            $data->courseid = $course;
            $data->name = $newname;

            $newgroupid = groups_create_group($data);
            $groupfor = $newgroupid;
            // Copy the MIPI instance, move and rename
            $result = duplicate_module($modinfo->get_course(), $modinfo->get_cm($source));
            $newmod = (int) $result->id;
            set_coursemodule_name($newmod, $newname);

            moveto_module($result, $modinfo->get_section_info($newsec));
            $restriction = \core_availability\tree::get_root_json([
                \availability_group\condition::get_json($groupfor)
            ], \core_availability\tree::OP_AND, false);
            $DB->set_field('course_modules', 'availability', json_encode($restriction), [
                'id' => $newmod
            ]);
            // find teacher

            rebuild_course_cache($course, true);
        }
        $teachers = array(
            $teacher
        );
        $teacherids = core_user_external::get_users_by_field('username', $teachers);
        $teacherid = $teacherids[0]['id'];

        $context = get_context_instance(CONTEXT_MODULE, $newmod);
        enrol_manual_external::enrol_users(array(
            array(
                'roleid' => 5,
                'userid' => $teacherid,
                'courseid' => $course
            )
        ));
        groups_add_member($groupfor, $teacherid);
        role_assign(3, $teacherid, $context->id);

        return array(
            'copyid' => $newmod,
            'groupid' => $groupfor
        );
    }

    public static function add_instance_returns()
    {
        return new external_single_structure(array(
            'copyid' => new external_value(PARAM_INT, 'module id of copy'),
            'groupid' => new external_value(PARAM_INT, 'id of new group'),
        ));
    }
}
;