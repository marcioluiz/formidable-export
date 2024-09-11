<?php
/**
 * Plugin Name: Formidable Export CSV CLI
 * Description: Export Formidable Form entries as a CSV file via WP-CLI.
 * Version: 0.1.2
 * Text Domain: formidable-export-csv
 * Author: Márcio Luiz
 *
 * @package 	Formidable_Export_CSV
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    class Formidable_Export_CLI_Command extends WP_CLI_Command {

        /**
         * Export Formidable form entries to a CSV file.
         *
         * ## OPTIONS
         *
         * [--form_id=<form_id>]
         * : The ID of the form to export.
         *
         * [--file_path=<file_path>]
         * : The path where the CSV file will be saved. Must be writable by the www-data user.
         *
         * [--start-date=<start-date>]
         * : The start date (Y-m-d) for filtering entries (optional).
         *
         * [--end-date=<end-date>]
         * : The end date (Y-m-d) for filtering entries (optional).
         *
         * ## EXAMPLES
         *
         *     wp formidable export_csv --form_id=123 --file_path=/path/to/save/file.csv --start-date=2023-01-01 --end-date=2023-12-31
         *
         * @when after_wp_load
         */
        public function export_csv( $args, $assoc_args ) {
            global $wpdb;

            // Ensure form_id is provided.
            if ( ! isset( $assoc_args['form_id'] ) || empty( $assoc_args['form_id'] ) ) {
                WP_CLI::error( 'You must provide a form ID using --form_id=<form_id>' );
            }

            $form_id = $assoc_args['form_id'];

            if ( ! class_exists( 'FrmEntry' ) ) {
                WP_CLI::error( 'Formidable Forms is not installed or activated.' );
            }

            // Handle file_path parameter or set a default.
            $file_path = isset( $assoc_args['file_path'] ) ? $assoc_args['file_path'] : ABSPATH . 'formidable-form-' . $form_id . '-entries-' . time() . '.csv';

            // Ensure the file path is writable.
            $dir = dirname( $file_path );
            if ( ! is_writable( $dir ) ) {
                WP_CLI::error( "The directory {$dir} is not writable by www-data. Please check the permissions." );
            }

            // Parse date parameters.
            $start_date = isset( $assoc_args['start-date'] ) ? $assoc_args['start-date'] : null;
            $end_date = isset( $assoc_args['end-date'] ) ? $assoc_args['end-date'] : null;

            if ( $start_date && ! $this->validate_date( $start_date ) ) {
                WP_CLI::error( 'Invalid start date format. Please use Y-m-d.' );
            }

            if ( $end_date && ! $this->validate_date( $end_date ) ) {
                WP_CLI::error( 'Invalid end date format. Please use Y-m-d.' );
            }

            // Get form fields
            $form_fields = FrmField::get_all_for_form( $form_id );
            if ( empty( $form_fields ) ) {
                WP_CLI::error( 'No fields found for this form.' );
            }

            // Define headers for CSV
            $headers = array();
            $field_keys = array();

            foreach ( $form_fields as $field ) {
                $headers[] = $field->name;
                $field_keys[$field->id] = $field->name;
            }

            // Add additional headers
            $headers = array_merge( $headers, array(
                'Marca Temporal',
                'Última Atualização',
                'IP',
                'ID Entrada',
                'Chave'
            ) );

            // Open the CSV file for writing.
            $file = fopen( $file_path, 'w' );
            if ( ! $file ) {
                WP_CLI::error( "Unable to open the file at {$file_path}. Please ensure the path is correct and writable." );
            }

            // Write the CSV header.
            fputcsv( $file, $headers );

            // Montar a query manualmente para garantir o uso correto dos operadores.
            $query = "SELECT it.*, fr.name as form_name, fr.form_key as form_key FROM {$wpdb->prefix}frm_items it 
                      LEFT OUTER JOIN {$wpdb->prefix}frm_forms fr ON it.form_id = fr.id
                      WHERE it.form_id = %d";

            // Adicionar condições de data, se fornecidas.
            if ( $start_date ) {
                $query .= $wpdb->prepare( " AND it.created_at >= %s", $start_date );
            }
            if ( $end_date ) {
                $query .= $wpdb->prepare( " AND it.created_at <= %s", $end_date );
            }

            // Executar a consulta.
            $entries = $wpdb->get_results( $wpdb->prepare( $query, $form_id ) );

            if ( empty( $entries ) ) {
                WP_CLI::error( 'No entries found for this form or date range.' );
            }

            // Process each entry.
            foreach ( $entries as $entry ) {
                WP_CLI::line( "Processando entrada ID: " . $entry->id );

                $row = array();
                // Add field values
                foreach ( $field_keys as $field_id => $field_name ) {
                    $field_value = $this->get_field_value( $entry->id, $field_id );
                    $row[] = $field_value;
                }

                // Add additional fields
                $row[] = $entry->created_at; // Marca Temporal
                $row[] = $entry->updated_at; // Última Atualização
                $row[] = $entry->ip; // IP
                $row[] = $entry->id; // ID Entrada
                $row[] = $entry->form_key; // Chave

                // Write the row to the CSV file.
                fputcsv( $file, $row );
            }

            // Close the CSV file.
            fclose( $file );

            WP_CLI::success( "Entradas do formulário exportadas com sucesso para: {$file_path}" );
        }

        /**
         * Get the field value from the entry.
         *
         * @param int $entry_id
         * @param int $field_id
         * @return string
         */
        private function get_field_value( $entry_id, $field_id ) {
            // Retrieve the field value using FrmEntryMeta::getAll().
            $meta_data = FrmEntryMeta::getAll( array( 'item_id' => $entry_id ) );

            $field_value = '';
            foreach ( $meta_data as $meta ) {
                if ( $meta->field_id == $field_id ) {
                    $field_value = $meta->meta_value;
                    break;
                }
            }

            return $field_value;
        }

        /**
         * Validate a date string in Y-m-d format.
         *
         * @param string $date
         * @return bool
         */
        private function validate_date( $date ) {
            $d = DateTime::createFromFormat( 'Y-m-d', $date );
            return $d && $d->format( 'Y-m-d' ) === $date;
        }
    }

    WP_CLI::add_command( 'formidable', 'Formidable_Export_CLI_Command' );
}

