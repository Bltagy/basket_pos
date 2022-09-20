<?php


/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @link       https://wiliex.com/
 * @since      1.0.0
 *
 * @package    Wiliex_Ajax
 * @subpackage Wiliex_Ajax/public
 * @author     Ahmed Bltagy <ahmed.bltagy@wiliex.com>
 */

namespace WILIEX_AJAX\Front;

class Wiliex_Ajax_Shortcode
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;


    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function timeline($atts, $content = '')
    {

        //         $group_ids = groups_get_user_groups(get_current_user_id());
        // foreach($group_ids["groups"] as $group_id) { 
        //         dd(groups_get_group(array( 'group_id' => $group_id )),1);
        // 	}
        //         dd('d');

        $modals = new \WILIEX_AJAX\Front\Forms\Wiliex_Ajax_Modals_Timeline($this->plugin_name, $this->version);

        add_action('wp_footer', array($modals, 'the_modals'));

        $attendance_form = new \WILIEX_AJAX\Front\Forms\Wiliex_Ajax_Form_Attendance($this->plugin_name, $this->version);

        add_action('wp_footer', array($attendance_form, 'the_form'));

        $behavior_form = new \WILIEX_AJAX\Front\Forms\Wiliex_Ajax_Form_Behavior($this->plugin_name, $this->version);

        add_action('wp_footer', array($behavior_form, 'the_form'));


        ob_start(); ?>
        <div class="flex actvity-head-bar wx-timeline-top-icons">
            <div id="whats-new-form" class="activity-form wx-create-something" data-toggle="modal" data-target="#wxCreateSomething">
                <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'public/images/Wiliex-512.png'; ?>" class="wx-createsomthing-img" alt="Learning">
                <h2>Create something!</h2>
            </div>
            <div class="subnav-filters filters no-ajax" id="subnav-filters">
                <div class="subnav-search clearfix">
                    <div class="dir-search activity-search bp-search" data-bp-search="activity">
                        <div class="">
                            <a> <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'public/images/Wiliex-GoLive.png'; ?>" class="img-section1-part1 wiliex-group-modal" alt="Learning"></a>
                            <a href="<?php echo bp_core_get_user_domain(get_current_user_id()); ?>"><img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'public/images/Wiliex-Home.png'; ?>" class="wx-home-img" alt="Learning"></a>
                        </div>
                    </div>
                </div>
            </div><!-- search & filters -->
        </div>

        <div name="whats-new-form" method="post" id="whats-new-form" class="activity-form wx-bb-action-icons-box">
            <div class="d-flex flex-row">
                <div class="flex-column wx-bb-action-icons">
                    <a data-toggle="modal" data-target="#wxMyCourses">
                        <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'public/images/Wiliex-Courses.png'; ?>">
                        <h2>Courses</h2>
                    </a>
                </div>
                <div class="flex-column wx-bb-action-icons">
                    <a data-toggle="modal" data-target="#wxMyGroups">
                        <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'public/images/Wiliex-Groups.png'; ?>">
                        <h2>Groups</h2>
                    </a>
                </div>
                <div class="flex-column wx-bb-action-icons">
                    <a>
                        <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'public/images/Dashboard-Wiliex-Calendars.png'; ?>">
                        <h2>Timetable</h2>
                    </a>
                </div>
                <div class="flex-column wx-bb-action-icons">
                    <a>
                        <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'public/images/Wiliex-Gradebook.png'; ?>">
                        <h2>Projects</h2>
                    </a>
                </div>
                <div class="flex-column wx-bb-action-icons">
                    <a>
                        <img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'public/images/Dashboard-Wiliex-Drive.png'; ?>">
                        <h2>Cloud</h2>
                    </a>
                </div>
            </div>
        </div>
    <?php

        $content = ob_get_clean();
        return $content;
    }

    public function lms_page($atts, $content = '')
    {
        $lms_page = new \WILIEX_AJAX\Front\Pages\Wiliex_Ajax_Page_LMS($this->plugin_name, $this->version);
        return $lms_page->the_page();
    }

    public function subjects_page($atts, $content = '')
    {
        $subjects = get_terms(
            'subjects',
            array(
                'hide_empty' => false,
            )
        );

        ob_start(); ?>
        <div class="row">
            <div class="col-sm-12">
                <a href="#" class="btn btn-primary float-right" data-toggle="modal" data-target="#wxSubjectsModalForm" data-action="create">
                    <i class="fa fa-plus"></i>
                    Create new
                </a>
            </div>
        </div>

        <div class="row subjects-edits-page">

            <?php foreach ($subjects as $subject) :
                $grades                  = get_field('grades_for_subjects', 'subjects_' . $subject->term_id);
                $is_optional = get_term_meta($subject->term_id, 'wx_optional', true)
            ?>
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title d-inline">
                                <?php echo $subject->name ?>
                            </h3>
                            <?php if ($is_optional) : ?>
                                <span class="badge badge-danger">Optional</span>
                            <?php endif; ?>
                            <p class="card-text">
                                <strong>Associated Grades:</strong>
                            <ul class="list-group list-group-horizontal-md flex-wrap">
                                <?php foreach ($grades as $grade) :
                                    $grade_term = get_term_by('term_id', $grade, 'grades'); ?>
                                    <li class="list-group-item"><?php echo $grade_term->name ?></li>
                                <?php endforeach ?>
                                <?php if (!$grades) : ?>
                                    No Associated Grads.
                                <?php endif; ?>
                            </ul>

                            </p>
                            <a href="#" class="btn btn-danger float-right" id="wx-subjects-delete" data-name="<?php echo $subject->name ?>" data-id="<?php echo $subject->term_id ?>">
                                <i class="fa fa-trash"></i>
                            </a>
                            <a href="#" class="btn btn-primary float-right" data-toggle="modal" data-target="#wxSubjectsModalForm" data-name="<?php echo $subject->name ?>" data-grades="<?php echo json_encode($grades) ?>" data-optional="<?php echo $is_optional ?>" data-ID="<?php echo $subject->term_id ?>" data-action="edit">
                                Edit
                            </a>

                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
        <?php

        $content = ob_get_clean();
        return $content;
    }

    public function attendance_page($atts, $content = '')
    {
        global $wpdb;
        $attendance_table = $wpdb->prefix . 'wx_attendance';
        $subject_form = new \WILIEX_AJAX\Front\Forms\Wiliex_Ajax_Form_Attendance($this->plugin_name, $this->version);

        add_action('wp_footer', array($subject_form, 'the_form'));
        ob_start();
        if ($atts['link-type'] == 'button') :
        ?>
            <a href="#" class="btn btn-primary float-right" data-toggle="modal" data-target="#wxAttendanceModalForm" data-action="create" data-name="New Attendance">
                <i class="fa fa-plus"></i>
                Attendance
            </a>
        <?php
        else :
        ?>
            <a href="#" data-toggle="modal" data-target="#wxAttendanceModalForm" data-action="create" data-name="New Attendance">
                Attendance
            </a>

        <?php
        endif;
        if ($atts['view-table'] == true) :
            $args           = array(
                'numberposts'     => -1,
                'post_type'  => 'wx_attendance',
                'post_status' => 'publish',
                'order'            => 'DESC',
                'orderby'          => 'modified',
            );
            $posts = get_posts($args);

        ?>

            <table id="example" class="table table-striped table-bordered" style="width:100%">

                <thead>
                    <tr>
                        <th>Class/Subject</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Taken By</th>
                        <th>Taken At</th>
                        <th>Modified On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as  $post) :
                        $record  = $wpdb->get_row("SELECT * FROM {$attendance_table} WHERE post_id = {$post->ID}", OBJECT);
                    ?>
                        <tr>
                            <td><?php echo $post->post_title; ?></td>
                            <td><?php echo $record->class_name; ?></td>
                            <td><?php echo $record->subject_name; ?></td>
                            <td><?php echo get_the_author_meta('display_name', $post->post_author); ?></td>
                            <td><?php echo date('Y-m-d h:i A', strtotime($post->post_date)); ?></td>
                            <td><?php echo date('Y-m-d h:i A', strtotime($post->post_modified)); ?></td>
                            <td>
                                <a href="#" class="btn btn-primary float-right" data-toggle="modal" data-target="#wxAttendanceModalForm" data-action="edit" data-id="<?php echo $post->ID; ?>" data-name="Edit Attendance(<?php echo $post->post_title; ?>)">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php
        endif;
        $content = ob_get_clean();
        return $content;
    }

    public function behavior_page($atts, $content = '')
    {
        global $wpdb;
        $attendance_table = $wpdb->prefix . 'wx_behavior';

        $behavior_form = new \WILIEX_AJAX\Front\Forms\Wiliex_Ajax_Form_Behavior($this->plugin_name, $this->version);

        add_action('wp_footer', array($behavior_form, 'the_form'));

        ob_start();
        if ($atts['link-type'] == 'button') :
        ?>
            <a href="#" class="btn btn-primary float-right" data-toggle="modal" data-target="#wxBehaviorModalForm" data-action="create" data-name="New Behavior">
                <i class="fa fa-plus"></i>
                Behaviour
            </a>
        <?php
        else :
        ?>
            <a href="#" data-toggle="modal" data-target="#wxBehaviorModalForm" data-action="create" data-name="New Behavior">
                Behaviour
            </a>

        <?php
        endif;
        if ($atts['view-table'] == true) :
            $args           = array(
                'numberposts'     => -1,
                'post_type'  => 'wx_behavior',
                'post_status' => 'publish',
                'order'            => 'DESC',
                'orderby'          => 'modified',
            );
            $posts = get_posts($args);

        ?>

            <table id="example" class="table table-striped table-bordered" style="width:100%">

                <thead>
                    <tr>
                        <th>Class/Subject</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Taken By</th>
                        <th>Taken At</th>
                        <th>Modified On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as  $post) :
                        $record  = $wpdb->get_row("SELECT * FROM {$attendance_table} WHERE post_id = {$post->ID}", OBJECT);
                    ?>
                        <tr>
                            <td><?php echo $post->post_title; ?></td>
                            <td><?php echo $record->class_name; ?></td>
                            <td><?php echo $record->subject_name; ?></td>
                            <td><?php echo get_the_author_meta('display_name', $post->post_author); ?></td>
                            <td><?php echo date('Y-m-d h:i A', strtotime($post->post_date)); ?></td>
                            <td><?php echo date('Y-m-d h:i A', strtotime($post->post_modified)); ?></td>
                            <td>
                                <a href="#" class="btn btn-primary float-right" data-toggle="modal" data-target="#wxBehaviorModalForm" data-action="edit" data-id="<?php echo $post->ID; ?>" data-name="Edit Behavior(<?php echo $post->post_title; ?>)">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php
        endif;
        $content = ob_get_clean();
        return $content;
    }

    public function lms_datatables($atts, $content = '')
    {
        $behavior_form = new \WILIEX_AJAX\Front\Forms\Wiliex_Ajax_Form_Behavior($this->plugin_name, $this->version);

        add_action('wp_footer', array($behavior_form, 'the_form'));
        add_action('wp_footer', array($behavior_form, 'the_view'));


        $attendance_form = new \WILIEX_AJAX\Front\Forms\Wiliex_Ajax_Form_Attendance($this->plugin_name, $this->version);

        add_action('wp_footer', array($attendance_form, 'the_form'));

        add_action('wp_footer', array($attendance_form, 'the_view'));

        ob_start();

        ?>


        <table id="wxDataTable" class="table table-striped table-bordered" style="width:100%">

            <thead>
                <tr>
                    <th>Item Title</th>
                    <th>Item Type</th>
                    <th>Teacher</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Created On</th>
                    <th>Modified</th>
                    <th width="80px">Actions</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

    <?php
        $content = ob_get_clean();
        return $content;
    }

    public function timeline_widget($atts, $content = '')
    {
        $current_user = wp_get_current_user();
        $display_name =  function_exists('bp_core_get_user_displayname') ? bp_core_get_user_displayname($current_user->ID) : $current_user->display_name;
        $user_type = function_exists('bp_get_user_member_type') ? bp_get_user_member_type(get_current_user_id()) : '';
        $query = 'field=Job Title' . '&user_id=' . get_current_user_id();
        $job_title = bp_get_profile_field_data($query);
        $query = 'field=Company' . '&user_id=' . get_current_user_id();
        $company = bp_get_profile_field_data($query);
    ?>

        <div class="bb-dash">

            <div class="flex align-items-center">
                <div class="bb-dash__avatar"><?php echo get_avatar(get_current_user_id()); ?></div>
                <div class="bb-dash__intro">
                    <h2 class="bb-dash__prior">
                        <span class="bb-dash__name"><?php echo $display_name; ?></span>
                        <span class="wxcode-member-type"><?php echo $user_type; ?></span>
                        <span class="wxcode-member-type"><span class="bp-member-type wxcode-job-title"><?php echo $job_title . '-' . $company; ?></span></span>
                    </h2>
                </div>
            </div>

        </div>

    <?php

        $content = ob_get_clean();
        return $content;
    }

    public function grade_book($atts, $content = '')
    {
        $gradebook = \WILIEX_AJAX\Front\Wiliex_Ajax_GradeBook::get(get_the_ID());

        if (!$gradebook) {
            return 'Gradebook data error!';
        }

    ?>

        <table id="wxGradeBookTable" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th colspan="2" rowspan="2">Student's ID</th>
                    <th rowspan="2">Student's Name</th>
                    <!-- <th rowspan="2">Minimum IGCSE Target</th> -->
                    <th colspan="9">Term 1</th>
                </tr>
                <tr>
                    <?php foreach ($gradebook['criteria'] as $key => $criteria) : ?>
                        <th><?php echo $criteria['name']; ?></th>
                    <?php endforeach; ?>
                    <th>Term 1 % Equivalent</th>
                    <th>Term 1 Grade</th>
                    <th>End of Year Percentage</th>
                    <th>End of Year Grade</th>
                </tr>
            </thead>

        </table>
        <?php if ($gradebook['grades']) : ?>
            <table class="table table-striped table-bordered dt-responsive nowrap" style="width:30%">
                <thead class="thead-dark">
                    <tr>
                        <th>Name</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Scale</th>
                    </tr>
                </thead>
                <?php foreach ($gradebook['grades'] as $key => $grades) : ?>
                    <tr>
                        <td><?php echo $grades['name']; ?></td>
                        <td><?php echo $grades['from']; ?></td>
                        <td><?php echo $grades['to']; ?></td>
                        <td><?php echo $grades['scale']; ?></td>
                    </tr>
                <?php endforeach; ?>

            </table>
        <?php endif; ?>
    <?php

        $content = ob_get_clean();
        return $content;
    }
    public function create_grade_book($atts, $content = '')
    {

        $grade_book_form = new \WILIEX_AJAX\Front\Forms\Wiliex_Ajax_Form_Gradebook($this->plugin_name, $this->version);

        add_action('wp_footer', array($grade_book_form, 'the_form'));


        ob_start();
    ?>
        <a href="#" class="btn btn-primary float-right" data-toggle="modal" data-target=".wxGradebookModal" data-action="create" data-name="New Gradebook">
            <i class="fa fa-plus"></i>
            New Gradebook
        </a>

        <table id="wxGradebookDataTable" class="table table-striped table-bordered .wxGradebookDataTable" style="width:100%">

            <thead>
                <tr>
                    <th>Item Title</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Created On</th>
                    <th width="80px">Actions</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

<?php
        $content = ob_get_clean();
        return $content;
    }
}
