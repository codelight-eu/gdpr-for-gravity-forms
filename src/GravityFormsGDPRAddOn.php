<?php

namespace Codelight\GDPR\Modules\GravityForms;

\GFForms::include_addon_framework();

class GravityFormsGDPRAddOn extends \GFAddOn
{
    protected $_version = GF_GDPR_VERSION;
    protected $_min_gravityforms_version = '2.1';
    protected $_slug = 'gdpr';
    protected $_path = 'gdpr-framework/src/Modules/GravityFormsGDPRAddOn.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms: GDPR Add-On';
    protected $_short_title = 'Privacy';

    private static $_instance = null;

    public static function get_instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new GravityFormsGDPRAddOn();
        }

        return self::$_instance;
    }

    public function init()
    {
        parent::init();
    }

    public function form_settings_fields($form)
    {
        $emailFields = [];
        foreach ($form['fields'] as $i => $field) {
            /* @var $field \GF_Field */
            if ('email' === $field->type) {
                $emailFields[$i]['label'] = $field->adminLabel ? $field->adminLabel : $field->label;
                $emailFields[$i]['value'] = $field->id;
            }
        }

        // Handle situations where the email field type is not 'email'
        if (empty($emailFields)) {
            foreach ($form['fields'] as $i => $field) {
                /* @var $field \GF_Field */
                $emailFields[$i]['label'] = $field->adminLabel ? $field->adminLabel : $field->label;
                $emailFields[$i]['value'] = $field->id;
            }
        }

        return [
            [
                'title'  => __('Privacy Settings', 'gdpr-admin'),
                'fields' => [
                    [
                        'label'   => __('Primary email address field', 'gdpr-admin'),
                        'type'    => 'select',
                        'name'    => 'gdpr_email_field',
                        'tooltip' => __("Select the field which will contain the customer's primary email address. This is used to identify which form submission belongs to which customer.", 'gdpr-admin'),
                        'choices' => $emailFields,
                    ],
                    [
                        'label'   => __('Exclude this form from automatic data download', 'gdpr-admin'),
                        'type'    => 'checkbox',
                        'name'    => 'gdpr_exclude_from_export',
                        'tooltip' => __("Check this if you want this form to be excluded when a customer's data is downloaded automatically.", 'gdpr-admin'),
                        'choices' => [
                            [
                                'label' => __('Exclude', 'gdpr-admin'),
                                'name'  => 'gdpr_exclude_from_export',
                            ],
                        ],
                    ],
                    [
                        'label'   => __('Exclude this form from automatic data deletion', 'gdpr-admin'),
                        'type'    => 'checkbox',
                        'name'    => 'gdpr_exclude_from_delete',
                        'tooltip' => __("Check this if you want this form to be excluded when a customer's data is deleted automatically.", 'gdpr-admin'),
                        'choices' => [
                            [
                                'label' => __('Exclude', 'gdpr-admin'),
                                'name'  => 'gdpr_exclude_from_delete',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}