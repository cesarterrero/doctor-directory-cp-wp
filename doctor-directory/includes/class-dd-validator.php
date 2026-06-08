<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DD_Validator {

    private $errors = [];
    private $data   = [];

    /**
     * Valida y sanitiza los datos del formulario.
     * $doctor_id se usa en edit para permitir el mismo email del registro actual.
     */
    public function validate( $raw, $doctor_id = 0 ) {
        $this->errors = [];
        $this->data   = [];

        // Nombre completo
        $name = sanitize_text_field( $raw['full_name'] ?? '' );
        if ( empty( $name ) ) {
            $this->errors['full_name'] = 'Full name is required.';
        } elseif ( mb_strlen( $name ) < 3 ) {
            $this->errors['full_name'] = 'Full name must be at least 3 characters.';
        } elseif ( mb_strlen( $name ) > 150 ) {
            $this->errors['full_name'] = 'Full name must not exceed 150 characters.';
        } else {
            $this->data['full_name'] = $name;
        }

        // Email
        $email = sanitize_email( $raw['email'] ?? '' );
        if ( empty( $email ) ) {
            $this->errors['email'] = 'Email address is required.';
        } elseif ( ! is_email( $email ) ) {
            $this->errors['email'] = 'Please enter a valid email address.';
        } elseif ( DD_Doctor::email_exists( $email, $doctor_id ) ) {
            $this->errors['email'] = 'This email is already registered.';
        } else {
            $this->data['email'] = $email;
        }

        // Dirección
        $address = sanitize_textarea_field( $raw['address'] ?? '' );
        if ( empty( $address ) ) {
            $this->errors['address'] = 'Physical address is required.';
        } elseif ( mb_strlen( $address ) < 5 ) {
            $this->errors['address'] = 'Please enter a complete address.';
        } else {
            $this->data['address'] = $address;
        }

        return $this;
    }

    public function passes() {
        return empty( $this->errors );
    }

    public function errors() {
        return $this->errors;
    }

    public function data() {
        return $this->data;
    }

    public function error_for( $field ) {
        return $this->errors[ $field ] ?? '';
    }
}
