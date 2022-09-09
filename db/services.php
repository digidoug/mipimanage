<?php
$services = array(
    'mipimanageservice' => array( // the name of the web service
        'functions' => array(
            'local_mipimanage_add_instance',
            'core_group_create_groups',
            'core_group_add_group_members',
            'core_user_get_users',
            'core_group_get_group_members',
            'enrol_manual_enrol_users',
            'mod_quiz_get_user_attempts',
            'mod_quiz_get_quizzes_by_courses',
            'mod_quiz_get_attempt_data',
            'mod_quiz_get_attempt_summary',
            'mod_quiz_get_attempt_review',
            'core_group_get_course_groups'
        ), // web service functions of this service
        'requiredcapability' => '', // if set, the web service user need this capability to access
                                     // any function of this service. For example: 'some/capability:specified'
        'restrictedusers' => 1, // if enabled, the Moodle administrator must link some user to this service
                                 // into the administration
        'enabled' => 1, // if enabled, the service can be reachable on a default installation
        'shortname' => 'mipimanage', // optional – but needed if restrictedusers is set so as to allow logins.
        'downloadfiles' => 1, // allow file downloads.
        'uploadfiles' => 1 // allow file uploads.
    )
);

$functions = array(
    'local_mipimanage_add_instance' => array( // web service function name
        'classname' => 'local_mipimanage_external', // class containing the external function OR namespaced class in classes/external/XXXX.php
        'methodname' => 'add_instance', // external function name
        'classpath' => 'local/mipimanage/externallib.php', // file containing the class/external function - not required if using namespaced auto-loading classes.
                                                            // defaults to the service's externalib.php
        'description' => 'Creates new mipi instance.', // human readable description of the web service function
        'type' => 'write', // database rights of the web service function (read, write)
        'ajax' => false, // is the service available to 'internal' ajax calls.
        'services' => array(
            MOODLE_OFFICIAL_MOBILE_SERVICE
        ), // Optional, only available for Moodle 3.1 onwards. List of built-in services (by shortname) where the function will be included. Services created manually via the Moodle interface are not supported.
        'capabilities' => '' // comma separated list of capabilities used by the function.
    )
);
