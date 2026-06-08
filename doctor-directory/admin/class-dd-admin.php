<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DD_Admin {

    private static $form_errors = [];
    private static $form_data   = [];
    private static $post_ran    = false;

    public function __construct() {
        add_action( 'admin_init',               [ $this, 'process_forms' ] );
        add_action( 'admin_menu',               [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts',    [ $this, 'enqueue_assets' ] );
        add_action( 'wp_ajax_dd_delete_doctor', [ $this, 'ajax_delete' ] );
        add_action( 'wp_ajax_dd_toggle_status', [ $this, 'ajax_toggle_status' ] );
    }

    public function process_forms() {
        if ( ( $_GET['page'] ?? '' ) !== 'doctor-directory' ) return;
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) return;

        $action = sanitize_key( $_GET['action'] ?? '' );

        if ( in_array( $action, [ 'add', 'edit' ], true ) ) {
            $this->handle_save( $action );
        } elseif ( $action === 'delete' ) {
            $this->handle_delete();
        }
    }

    private function handle_save( $action ) {
        $is_edit   = ( $action === 'edit' );
        $doctor_id = absint( $_POST['doctor_id'] ?? 0 );
        $page_url  = admin_url( 'admin.php?page=doctor-directory' );

        if ( ! isset( $_POST['dd_form_nonce'] ) ||
             ! wp_verify_nonce( sanitize_key( $_POST['dd_form_nonce'] ), 'dd_save_doctor' ) ) {
            wp_die( 'Security check failed.', 'Error', [ 'response' => 403, 'back_link' => true ] );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Permission denied.', '', [ 'response' => 403 ] );
        }

        $validator = new DD_Validator();
        $validator->validate( $_POST, $is_edit ? $doctor_id : 0 );

        if ( $validator->passes() ) {
            if ( $is_edit ) {
                $ok     = DD_Doctor::update( $doctor_id, $validator->data() );
                $notice = $ok ? 'updated' : 'error';
            } else {
                $new_id = DD_Doctor::insert( $validator->data() );
                $notice = $new_id ? 'added' : 'error';
            }
            wp_redirect( add_query_arg( 'notice', $notice, $page_url ) );
            exit;
        }

        self::$post_ran    = true;
        self::$form_errors = $validator->errors();
        self::$form_data   = [
            'full_name' => sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) ),
            'email'     => sanitize_email( wp_unslash( $_POST['email']     ?? '' ) ),
            'address'   => sanitize_textarea_field( wp_unslash( $_POST['address']   ?? '' ) ),
        ];
    }

    private function handle_delete() {
        $doctor_id = absint( $_POST['doctor_id'] ?? 0 );
        $page_url  = admin_url( 'admin.php?page=doctor-directory' );

        if ( ! $doctor_id ) {
            wp_redirect( add_query_arg( 'notice', 'error', $page_url ) );
            exit;
        }

        if ( ! isset( $_POST['dd_delete_nonce'] ) ||
             ! wp_verify_nonce( sanitize_key( $_POST['dd_delete_nonce'] ), 'dd_delete_doctor_' . $doctor_id ) ) {
            wp_die( 'Security check failed.', 'Error', [ 'response' => 403, 'back_link' => true ] );
        }

        $notice = DD_Doctor::delete( $doctor_id ) ? 'deleted' : 'error';
        wp_redirect( add_query_arg( 'notice', $notice, $page_url ) );
        exit;
    }

    public static function get_form_errors()    { return self::$form_errors; }
    public static function get_form_data()      { return self::$form_data; }
    public static function post_was_processed() { return self::$post_ran; }

    public function register_menu() {
        add_menu_page(
            'Doctor Directory',
            'Doctor Directory',
            'manage_options',
            'doctor-directory',
            [ $this, 'dispatch' ],
            'dashicons-heart',
            30
        );
    }

    public function enqueue_assets( $hook ) {
        if ( $hook !== 'toplevel_page_doctor-directory' ) return;

        wp_register_script( 'dd-jquery-validate', DD_PLUGIN_URL . 'admin/assets/js/jquery.validate.min.js', [ 'jquery' ], '1.21.0', true );
        wp_enqueue_style( 'dd-admin', DD_PLUGIN_URL . 'admin/assets/css/dd-admin.css', [], DD_VERSION );
        wp_enqueue_script( 'dd-admin', DD_PLUGIN_URL . 'admin/assets/js/dd-admin.js', [ 'jquery', 'dd-jquery-validate' ], DD_VERSION, true );

        wp_localize_script( 'dd-admin', 'DD', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'dd_ajax_nonce' ),
            'i18n'    => [
                'deleting'      => 'Deleting...',
                'deleted'       => 'Doctor deleted successfully.',
                'error'         => 'An error occurred. Please try again.',
                'fieldRequired' => 'This field is required.',
                'emailInvalid'  => 'Please enter a valid email address.',
                'deactivated'   => 'Doctor deactivated.',
                'activated'     => 'Doctor activated.',
            ],
        ] );
    }

    public function dispatch() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Permission denied.', '', [ 'response' => 403 ] );
        }

        $action    = sanitize_key( $_GET['action'] ?? 'list' );
        $views_dir = DD_PLUGIN_DIR . 'admin/views/';

        $views = [
            'list'   => $views_dir . 'view-list.php',
            'add'    => $views_dir . 'view-form.php',
            'edit'   => $views_dir . 'view-form.php',
            'delete' => $views_dir . 'view-delete-confirm.php',
        ];

        require_once $views[ $action ] ?? $views['list'];
    }

    public function ajax_delete() {
        check_ajax_referer( 'dd_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }

        $id = absint( $_POST['id'] ?? 0 );

        if ( ! $id || ! DD_Doctor::get_by_id( $id ) ) {
            wp_send_json_error( [ 'message' => 'Doctor not found.' ], 404 );
        }

        DD_Doctor::delete( $id )
            ? wp_send_json_success( [ 'message' => 'Doctor deleted.' ] )
            : wp_send_json_error( [ 'message' => 'Could not delete record.' ], 500 );
    }

    public function ajax_toggle_status() {
        check_ajax_referer( 'dd_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ], 403 );
        }

        $id     = absint( $_POST['id'] ?? 0 );
        $result = $id ? DD_Doctor::toggle_status( $id ) : false;

        if ( $result === false ) {
            wp_send_json_error( [ 'message' => 'Could not update status.' ], 500 );
        }

        wp_send_json_success( [ 'status' => $result ] );
    }
}
