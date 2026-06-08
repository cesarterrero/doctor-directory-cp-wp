<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DD_Database {

    /**
     * Crea la tabla del plugin usando dbDelta (idempotente, seguro en re-activaciones).
     * Nota: dbDelta requiere dos espacios antes de PRIMARY KEY y UNIQUE KEY.
     */
    public static function create_table() {
        global $wpdb;

        $table   = $wpdb->prefix . DD_TABLE;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name  VARCHAR(150) NOT NULL,
  email      VARCHAR(255) NOT NULL,
  address    TEXT NOT NULL,
  status     TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY uq_dd_email (email)
) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( 'dd_db_version', DD_VERSION );

        // Solo inserta datos de ejemplo en una instalación nueva (tabla vacía)
        if ( (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) === 0 ) {
            self::seed_sample_data();
        }
    }

    private static function seed_sample_data() {
        global $wpdb;
        $table = $wpdb->prefix . DD_TABLE;

        $doctors = [
            [ 'Dr. Carlos Méndez Reyes',   'carlos.mendez@clinicasanar.com.do',  'Av. Winston Churchill 1099, Piantini, Santo Domingo'   ],
            [ 'Dra. María Fernanda López',  'mflopez@hospitalprivado.com.do',     'Calle El Conde 45, Zona Colonial, Santo Domingo'       ],
            [ 'Dr. Roberto Almonte Cruz',   'ralmonte@centromedico.com.do',       'Av. John F. Kennedy Km 6.5, Los Prados, Santo Domingo' ],
            [ 'Dra. Luisa Estévez Vargas',  'lestevez@clinicabelen.com.do',       'Av. Independencia 701, Gazcue, Santo Domingo'          ],
            [ 'Dr. Antonio Jiménez Peña',   'ajimenez@medicaribe.com.do',         'Calle Duarte 34, Santiago de los Caballeros'           ],
        ];

        foreach ( $doctors as $doctor ) {
            $wpdb->insert(
                $table,
                [ 'full_name' => $doctor[0], 'email' => $doctor[1], 'address' => $doctor[2], 'status' => 1 ],
                [ '%s', '%s', '%s', '%d' ]
            );
        }
    }

    public static function drop_table() {
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}dd_doctors`" );
        delete_option( 'dd_db_version' );
    }
}
