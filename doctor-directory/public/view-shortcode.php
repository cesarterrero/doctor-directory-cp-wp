<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$doctors = DD_Doctor::get_active();
?>

<div class="dd-directory">

    <?php if ( empty( $doctors ) ) : ?>
        <p class="dd-directory-empty">No doctors available at this time.</p>
    <?php else : ?>
        <div class="dd-cards">
            <?php foreach ( $doctors as $doctor ) : ?>
            <div class="dd-card">
                <div class="dd-card-avatar">
                    <?php echo esc_html( strtoupper( substr( $doctor->full_name, 0, 2 ) ) ); ?>
                </div>
                <div class="dd-card-body">
                    <h3 class="dd-card-name"><?php echo esc_html( $doctor->full_name ); ?></h3>
                    <p class="dd-card-email">
                        <a href="mailto:<?php echo esc_attr( $doctor->email ); ?>">
                            <?php echo esc_html( $doctor->email ); ?>
                        </a>
                    </p>
                    <p class="dd-card-address"><?php echo esc_html( $doctor->address ); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
