<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DD_Doctor {

    private static function table() {
        global $wpdb;
        return $wpdb->prefix . DD_TABLE;
    }

    /**
     * Obtiene todos los médicos. Acepta filtro por status: 'all', 'active', 'inactive'.
     */
    public static function get_all( $search = '', $status = 'all' ) {
        global $wpdb;
        $table = self::table();

        $where = [];
        $values = [];

        if ( $status === 'active' ) {
            $where[] = 'status = 1';
        } elseif ( $status === 'inactive' ) {
            $where[] = 'status = 0';
        }

        if ( $search !== '' ) {
            $like      = '%' . $wpdb->esc_like( $search ) . '%';
            $where[]   = '(full_name LIKE %s OR email LIKE %s OR address LIKE %s)';
            $values[]  = $like;
            $values[]  = $like;
            $values[]  = $like;
        }

        $sql = "SELECT * FROM {$table}";
        if ( $where ) {
            $sql .= ' WHERE ' . implode( ' AND ', $where );
        }
        $sql .= ' ORDER BY full_name ASC';

        if ( $values ) {
            return $wpdb->get_results( $wpdb->prepare( $sql, $values ) ) ?: [];
        }

        return $wpdb->get_results( $sql ) ?: [];
    }

    /** Solo médicos activos — usado por el shortcode público. */
    public static function get_active( $search = '' ) {
        return self::get_all( $search, 'active' );
    }

    public static function get_by_id( $id ) {
        global $wpdb;
        $table = self::table();
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id )
        ) ?: null;
    }

    public static function insert( $data ) {
        global $wpdb;
        $result = $wpdb->insert(
            self::table(),
            [
                'full_name' => $data['full_name'],
                'email'     => $data['email'],
                'address'   => $data['address'],
                'status'    => 1,
            ],
            [ '%s', '%s', '%s', '%d' ]
        );
        return $result ? $wpdb->insert_id : false;
    }

    public static function update( $id, $data ) {
        global $wpdb;
        return $wpdb->update(
            self::table(),
            [
                'full_name'  => $data['full_name'],
                'email'      => $data['email'],
                'address'    => $data['address'],
                'updated_at' => current_time( 'mysql' ),
            ],
            [ 'id' => $id ],
            [ '%s', '%s', '%s', '%s' ],
            [ '%d' ]
        ) !== false;
    }

    public static function delete( $id ) {
        global $wpdb;
        return $wpdb->delete( self::table(), [ 'id' => $id ], [ '%d' ] ) !== false;
    }

    /** Alterna el status entre activo (1) e inactivo (0). */
    public static function toggle_status( $id ) {
        global $wpdb;
        $table  = self::table();
        $doctor = self::get_by_id( $id );
        if ( ! $doctor ) return false;

        $new_status = $doctor->status ? 0 : 1;

        return $wpdb->update(
            $table,
            [ 'status' => $new_status, 'updated_at' => current_time( 'mysql' ) ],
            [ 'id' => $id ],
            [ '%d', '%s' ],
            [ '%d' ]
        ) !== false ? $new_status : false;
    }

    public static function count() {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . self::table() );
    }

    public static function email_exists( $email, $exclude_id = 0 ) {
        global $wpdb;
        $table = self::table();
        return (int) $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE email = %s AND id != %d", $email, $exclude_id )
        ) > 0;
    }
}
