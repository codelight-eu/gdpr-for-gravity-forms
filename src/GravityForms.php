<?php

namespace Codelight\GDPR\Modules\GravityForms;

use GFAPI;

/**
 * todo: add filters!
 *
 * Class GravityForms
 *
 * @package Codelight\GDPR\Modules\GravityForms
 */
class GravityForms
{
    public function __construct()
    {
        define('GF_GDPR_VERSION', '1.0');

        add_action('gform_loaded', [$this, 'loadAddOn'], 5);
        add_filter('gdpr/data-subject/data', [$this, 'getExportData'], 20, 2);
        add_action('gdpr/data-subject/delete', [$this, 'deleteEntries']);
        add_action('gdpr/data-subject/anonymize', [$this, 'deleteEntries']);
    }

    public function loadAddOn()
    {
        if (!method_exists('\GFForms', 'include_addon_framework')) {
            return;
        }

        \GFAddOn::register('\Codelight\GDPR\Modules\GravityForms\GravityFormsGDPRAddOn');
    }

    public function getExportData(array $data, $email)
    {
        $forms = $this->getValidForms($this->getForms(), 'export');
        if (!count($forms)) {
            return $data;
        }

        foreach ($forms as $form) {

            $entries = $this->getEntriesByEmail($form, $email);

            if (!count($entries)) {
                continue;
            }

            $title  = __('Form submission:', 'gdpr') . ' ' . $form['title'];
            $fields = $this->getFormFields($form);

            foreach ($entries as $entry) {

                foreach ($fields as $field) {
                    $data[$title][$field['label']] = $entry[$field['id']];
                }

                $data[$title]['date']       = $entry['date_created'];
                $data[$title]['ip']         = $entry['ip'];
                $data[$title]['url']        = $entry['source_url'];
                $data[$title]['user_agent'] = $entry['user_agent'];
            }
        }

        return $data;
    }

    public function deleteEntries($email)
    {
        $forms = $this->getValidForms($this->getForms(), 'delete');
        if (!count($forms)) {
            return;
        }

        foreach ($forms as $form) {

            $entries = $this->getEntriesByEmail($form, $email);

            if (!count($entries)) {
                continue;
            }

            foreach ($entries as $entry) {
                GFAPI::delete_entry($entry['id']);
            }
        }
    }

    public function getForms()
    {
        return GFAPI::get_forms();
    }

    public function getValidForms($forms, $action)
    {
        $gdprAddOn  = GravityFormsGDPRAddOn::get_instance();
        $validForms = [];

        foreach ($forms as $form) {
            $settings = $gdprAddOn->get_form_settings($form);

            // Skip if email is not set
            if (!isset($settings['gdpr_email_field'])) {
                continue;
            }

            if ('delete' === $action) {
                if (isset($settings['gdpr_exclude_from_delete']) && $settings['gdpr_exclude_from_delete']) {
                    continue;
                }
            } else if ('export' === $action) {
                if (isset($settings['gdpr_exclude_from_export']) && $settings['gdpr_exclude_from_export']) {
                    continue;
                }
            }

            $validForms[] = $form;
        }

        return $validForms;
    }

    public function getEntriesByEmail($form, $email)
    {
        $gdprAddOn           = GravityFormsGDPRAddOn::get_instance();
        $primaryEmailFieldId = $gdprAddOn->get_form_settings($form)['gdpr_email_field'];

        $filter = [
            'field_filters' => [
                [
                    'key'   => $primaryEmailFieldId,
                    'value' => $email,
                ],
            ],
        ];
        $paging = [
            'offset'    => 0,
            'page_size' => 200,
        ];

        $entries = GFAPI::get_entries(
            $form['id'],
            $filter,
            null,
            $paging
        );

        // todo: add check for count: $result = GFAPI::count_entries( $form_ids, $search_criteria );

        return $entries;
    }

    public function getFormFields($form)
    {
        $fields = [];

        if (!count($form['fields'])) {
            return $fields;
        }

        foreach ($form['fields'] as $field) {
            $fields[] = [
                'id'    => $field->id,
                'label' => $field->label,
            ];
        }

        return $fields;
    }
}