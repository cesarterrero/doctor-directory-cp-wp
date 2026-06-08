<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$notices = [
    'added'   => [ 'success', 'Doctor added successfully.'   ],
    'updated' => [ 'success', 'Doctor updated successfully.' ],
    'deleted' => [ 'success', 'Doctor deleted successfully.' ],
    'error'   => [ 'error',   'An error occurred. Please try again.' ],
];

$notice_type = sanitize_key( $_GET['notice'] ?? '' );
$search      = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
$doctors     = DD_Doctor::get_all( $search );
$total       = DD_Doctor::count();
$base_url    = admin_url( 'admin.php?page=doctor-directory' );
$add_url     = add_query_arg( 'action', 'add', $base_url );
?>

<div class="wrap dd-wrap">

    <h1 class="wp-heading-inline">Doctor Directory</h1>
    <a href="<?php echo esc_url( $add_url ); ?>" class="page-title-action">+ Add New Doctor</a>
    <hr class="wp-header-end">

    <?php if ( isset( $notices[ $notice_type ] ) ) : ?>
        <div class="notice notice-<?php echo esc_attr( $notices[ $notice_type ][0] ); ?> is-dismissible">
            <p><?php echo esc_html( $notices[ $notice_type ][1] ); ?></p>
        </div>
    <?php endif; ?>

    <form id="dd-search-form" method="get">
        <input type="hidden" name="page" value="doctor-directory">
        <p class="search-box">
            <label class="screen-reader-text" for="dd-search-input">Search doctors</label>
            <input type="search" id="dd-search-input" name="s"
                value="<?php echo esc_attr( $search ); ?>"
                placeholder="Search by name, email or address…"
                class="dd-search-input">
            <input type="submit" class="button" value="Search">
            <?php if ( $search ) : ?>
                <a href="<?php echo esc_url( $base_url ); ?>" class="button dd-clear-search">✕ Clear</a>
            <?php endif; ?>
        </p>
    </form>

    <div class="dd-stats-bar">
        <?php if ( $search ) : ?>
            Showing <strong><?php echo count( $doctors ); ?></strong> result(s) for
            <em>"<?php echo esc_html( $search ); ?>"</em> &mdash; <?php echo esc_html( $total ); ?> total
        <?php else : ?>
            <strong><?php echo esc_html( $total ); ?></strong> doctor(s) registered
        <?php endif; ?>
    </div>

    <table class="wp-list-table widefat fixed striped dd-doctors-table">
        <thead>
            <tr>
                <th class="column-name">Full Name</th>
                <th class="column-email">Email Address</th>
                <th class="column-address">Physical Address</th>
                <th class="column-status">Status</th>
                <th class="column-actions">Actions</th>
            </tr>
        </thead>

        <tbody id="dd-doctors-tbody">
        <?php if ( empty( $doctors ) ) : ?>
            <tr class="dd-no-results">
                <td colspan="5">
                    <?php if ( $search ) : ?>
                        No results for <strong>"<?php echo esc_html( $search ); ?>"</strong>.
                        <a href="<?php echo esc_url( $base_url ); ?>">Clear search</a>
                    <?php else : ?>
                        No doctors registered yet. <a href="<?php echo esc_url( $add_url ); ?>">Add the first one</a>.
                    <?php endif; ?>
                </td>
            </tr>
        <?php else : ?>
            <?php foreach ( $doctors as $doctor ) :
                $is_active  = (int) $doctor->status === 1;
                $edit_url   = add_query_arg( [ 'action' => 'edit',   'id' => $doctor->id ], $base_url );
                $delete_url = add_query_arg( [ 'action' => 'delete', 'id' => $doctor->id ], $base_url );
            ?>
            <tr id="dd-row-<?php echo esc_attr( $doctor->id ); ?>"
                class="dd-doctor-row<?php echo $is_active ? '' : ' dd-row-inactive'; ?>">

                <td class="column-name">
                    <strong>
                        <a href="<?php echo esc_url( $edit_url ); ?>">
                            <?php echo esc_html( $doctor->full_name ); ?>
                        </a>
                    </strong>
                </td>
                <td class="column-email">
                    <a href="mailto:<?php echo esc_attr( $doctor->email ); ?>">
                        <?php echo esc_html( $doctor->email ); ?>
                    </a>
                </td>
                <td class="column-address"><?php echo esc_html( $doctor->address ); ?></td>
                <td class="column-status">
                    <span class="dd-status-badge <?php echo $is_active ? 'dd-status-active' : 'dd-status-inactive'; ?>">
                        <?php echo $is_active ? 'Active' : 'Inactive'; ?>
                    </span>
                </td>
                <td class="column-actions">
                    <a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small">Edit</a>
                    <div class="dd-row-actions">
                        <button type="button" class="dd-link-action dd-action-toggle"
                            data-id="<?php echo esc_attr( $doctor->id ); ?>"
                            data-status="<?php echo esc_attr( $doctor->status ); ?>">
                            <?php echo $is_active ? 'Deactivate' : 'Activate'; ?>
                        </button>
                        <span class="dd-sep">|</span>
                        <button type="button" class="dd-link-action dd-link-danger dd-action-delete"
                            data-id="<?php echo esc_attr( $doctor->id ); ?>"
                            data-name="<?php echo esc_attr( $doctor->full_name ); ?>"
                            data-fallback="<?php echo esc_url( $delete_url ); ?>">
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>

        <tfoot>
            <tr>
                <th class="column-name">Full Name</th>
                <th class="column-email">Email Address</th>
                <th class="column-address">Physical Address</th>
                <th class="column-status">Status</th>
                <th class="column-actions">Actions</th>
            </tr>
        </tfoot>
    </table>

    <p class="description" style="margin-top:8px">
        Shortcode to display the public directory: <code>[doctor_directory]</code>
    </p>

</div>

<div id="dd-delete-modal" class="dd-modal" role="dialog" aria-modal="true" aria-labelledby="dd-modal-title" hidden>
    <div class="dd-modal-backdrop"></div>
    <div class="dd-modal-box">
        <h2 id="dd-modal-title">Confirm Deletion</h2>
        <p>Are you sure you want to delete <strong id="dd-modal-name"></strong>? This action cannot be undone.</p>
        <div class="dd-modal-actions">
            <button type="button" id="dd-modal-confirm" class="button button-primary dd-btn-danger">Delete</button>
            <button type="button" id="dd-modal-cancel" class="button">Cancel</button>
        </div>
        <div id="dd-modal-feedback" aria-live="polite"></div>
    </div>
</div>
