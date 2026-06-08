<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$is_edit   = ( $action === 'edit' );
$doctor_id = absint( $_GET['id'] ?? 0 );
$page_url  = admin_url( 'admin.php?page=doctor-directory' );
$form_url  = add_query_arg(
    array_filter( [ 'page' => 'doctor-directory', 'action' => $action, 'id' => $is_edit ? $doctor_id : null ] ),
    admin_url( 'admin.php' )
);

$doctor = null;
if ( $is_edit ) {
    $doctor = DD_Doctor::get_by_id( $doctor_id );
    if ( ! $doctor ) {
        echo '<div class="wrap"><div class="notice notice-error"><p>Doctor not found. <a href="' . esc_url( $page_url ) . '">Back to list</a></p></div></div>';
        return;
    }
}

$errors = DD_Admin::get_form_errors();

if ( DD_Admin::post_was_processed() ) {
    $form_data = DD_Admin::get_form_data();
} else {
    $form_data = [
        'full_name' => $doctor->full_name ?? '',
        'email'     => $doctor->email     ?? '',
        'address'   => $doctor->address   ?? '',
    ];
}

$title = $is_edit ? 'Edit Doctor' : 'Add New Doctor';
?>

<div class="wrap dd-wrap">

    <h1><?php echo esc_html( $title ); ?></h1>
    <hr class="wp-header-end">

    <?php if ( ! empty( $errors ) ) : ?>
        <div class="notice notice-error">
            <p><strong>Please fix the errors below before saving.</strong></p>
        </div>
    <?php endif; ?>

    <form id="dd-doctor-form" method="post" action="<?php echo esc_url( $form_url ); ?>" novalidate>

        <?php wp_nonce_field( 'dd_save_doctor', 'dd_form_nonce' ); ?>

        <?php if ( $is_edit ) : ?>
            <input type="hidden" name="doctor_id" value="<?php echo esc_attr( $doctor_id ); ?>">
        <?php endif; ?>

        <table class="form-table dd-form-table" role="presentation">

            <tr>
                <th scope="row">
                    <label for="full_name">Full Name <span class="dd-required">*</span></label>
                </th>
                <td>
                    <input type="text" id="full_name" name="full_name"
                        class="regular-text<?php echo isset( $errors['full_name'] ) ? ' dd-input-error' : ''; ?>"
                        value="<?php echo esc_attr( $form_data['full_name'] ); ?>"
                        maxlength="150" autocomplete="name">
                    <span class="dd-error-msg" id="full_name-error" aria-live="polite">
                        <?php echo isset( $errors['full_name'] ) ? esc_html( $errors['full_name'] ) : ''; ?>
                    </span>
                    <p class="description">3–150 characters.</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="email">Email Address <span class="dd-required">*</span></label>
                </th>
                <td>
                    <input type="email" id="email" name="email"
                        class="regular-text<?php echo isset( $errors['email'] ) ? ' dd-input-error' : ''; ?>"
                        value="<?php echo esc_attr( $form_data['email'] ); ?>"
                        maxlength="255" autocomplete="email">
                    <span class="dd-error-msg" id="email-error" aria-live="polite">
                        <?php echo isset( $errors['email'] ) ? esc_html( $errors['email'] ) : ''; ?>
                    </span>
                    <p class="description">Must be unique across all records.</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="address">Physical Address <span class="dd-required">*</span></label>
                </th>
                <td>
                    <textarea id="address" name="address" rows="4" cols="50"
                        class="large-text<?php echo isset( $errors['address'] ) ? ' dd-input-error' : ''; ?>"
                    ><?php echo esc_textarea( $form_data['address'] ); ?></textarea>
                    <span class="dd-error-msg" id="address-error" aria-live="polite">
                        <?php echo isset( $errors['address'] ) ? esc_html( $errors['address'] ) : ''; ?>
                    </span>
                </td>
            </tr>

        </table>

        <p class="dd-form-actions">
            <?php submit_button( $is_edit ? 'Update Doctor' : 'Add Doctor', 'primary', 'submit', false ); ?>
            <a href="<?php echo esc_url( $page_url ); ?>" class="button dd-cancel-btn">Cancel</a>
            <span class="dd-submit-feedback" aria-live="polite"></span>
        </p>

    </form>

</div>
