<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$page_url  = admin_url( 'admin.php?page=doctor-directory' );
$doctor_id = absint( $_GET['id'] ?? 0 );
$doctor    = $doctor_id ? DD_Doctor::get_by_id( $doctor_id ) : null;

if ( ! $doctor ) {
    echo '<div class="wrap"><div class="notice notice-error"><p>Doctor not found. <a href="' . esc_url( $page_url ) . '">Back to list</a></p></div></div>';
    return;
}

$confirm_url = add_query_arg( [ 'page' => 'doctor-directory', 'action' => 'delete', 'id' => $doctor_id ], admin_url( 'admin.php' ) );
$edit_url    = add_query_arg( [ 'page' => 'doctor-directory', 'action' => 'edit',   'id' => $doctor_id ], admin_url( 'admin.php' ) );
?>

<div class="wrap dd-wrap">

    <h1>Delete Doctor</h1>
    <hr class="wp-header-end">

    <div class="dd-confirm-box">
        <div class="dd-confirm-icon" aria-hidden="true">⚠</div>
        <h2 class="dd-confirm-title">Are you sure?</h2>
        <p>You are about to permanently delete the following record. <strong>This cannot be undone.</strong></p>

        <table class="dd-confirm-details">
            <tr>
                <th>Full Name</th>
                <td><?php echo esc_html( $doctor->full_name ); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo esc_html( $doctor->email ); ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo esc_html( $doctor->address ); ?></td>
            </tr>
        </table>

        <form method="post" action="<?php echo esc_url( $confirm_url ); ?>">
            <?php wp_nonce_field( 'dd_delete_doctor_' . $doctor_id, 'dd_delete_nonce' ); ?>
            <input type="hidden" name="doctor_id" value="<?php echo esc_attr( $doctor_id ); ?>">

            <div class="dd-confirm-actions">
                <?php submit_button( 'Yes, delete permanently', 'primary dd-btn-danger', 'submit', false ); ?>
                <a href="<?php echo esc_url( $page_url ); ?>" class="button">Cancel</a>
                <a href="<?php echo esc_url( $edit_url ); ?>" class="button">Edit instead</a>
            </div>
        </form>
    </div>

</div>
