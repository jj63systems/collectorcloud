<?php

return [

    /*
    |--------------------------------------------------------------------------
    | System Settings Definition
    |--------------------------------------------------------------------------
    | Each top-level key is a setting group.
    | Each group contains a label, display order, and an array of settings.
    | Each setting defines label, default, type, presentation, options, etc.
    |--------------------------------------------------------------------------
    */

    'appearance' => [
        'label' => 'Appearance',
        'order' => 10,
        'settings' => [

            'lang' => [
                'label' => 'Language',
                'order' => 10,
                'default' => 'en',
                'presentation' => 'select',
                'options' => [
                    'English' => 'en',
                    'French' => 'fr',
                    'German' => 'de',
                ],
                'description' => 'Choose the language for the system.',
            ],

            'textsettings' => [
                'label' => 'Alter screen texts',
                'order' => 20,
                'default' => true,
                'presentation' => 'boolean',
                'description' => 'Choose whether to use your own terminology for field labels and other texts.',
            ],

            'color_scheme' => [
                'label' => 'Color Scheme',
                'order' => 30,
//                'default' => 'grey',
                'presentation' => 'select',
                'options' => [
                    'Sky' => 'sky',
                    'Blue' => 'blue',
                    'Indigo' => 'indigo',
                    'Purple' => 'purple',
                    'Pink' => 'pink',
                    'Rose' => 'rose',
                    'Red' => 'red',
                    'Orange' => 'orange',
                    'Amber' => 'amber',
//                    'Yellow' => 'yellow',
                    'Lime' => 'lime',
                    'Green' => 'green',
                    'Emerald' => 'emerald',
                    'Teal' => 'teal',
                    'Cyan' => 'cyan',
                    'Slate' => 'slate',
                    'Gray' => 'gray',
                    'Zinc' => 'zinc',
                ],
                'description' => 'Choose the color scheme for the system.',
            ],

        ],
    ],

    'ai' => [
        'label' => 'AI Preferences',
        'order' => 30,
        'settings' => [

            'model' => [
                'label' => 'Preferred AI Model',
                'order' => 10,
                'default' => 'gpt-4o',
                'presentation' => 'select',
                'options' => [
                    'gpt-4o' => 'OpenAI GPT-4 Omni',
                    'gpt-4-turbo' => 'OpenAI GPT-4 Turbo',
                    'claude-3-opus' => 'Anthropic Claude 3 Opus',
                ],
            ],

            'context_window' => [
                'label' => 'Max Context Tokens',
                'order' => 20,
                'default' => 4096,
                'presentation' => 'number',
            ],

            'enable_experimental_features' => [
                'label' => 'Enable Experimental AI Features',
                'order' => 30,
                'default' => false,
                'presentation' => 'boolean',
            ],
        ],
    ],

];
