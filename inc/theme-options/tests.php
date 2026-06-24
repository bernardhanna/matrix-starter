<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$tests = new FieldsBuilder('theme_tests', [
    'label' => 'Theme Tests',
]);

$tests
    ->addMessage('theme_tests_intro', 'Theme Tests', [
        'label'   => 'Theme Tests',
        'message' => 'Run PHP checks from this tab or enable “Run tests on save”. Includes registration validation and optional live login/register when <code>MATRIX_TEST_USER_EMAIL</code>, <code>MATRIX_TEST_USER_PASSWORD</code>, or <code>MATRIX_TEST_REGISTER=1</code> are set. Browser tests: <code>npm run test:e2e:myaccount</code> and <code>npm run test:e2e:myaccount:live</code>.',
    ])
    ->addField('theme_tests_panel', 'message', [
        'label'   => 'Run Tests',
        'message' => '',
        'wrapper' => ['class' => 'matrix-theme-tests-field'],
    ])
    ->addGroup('theme_test_controls', [
        'label' => 'Automated Checks',
    ])
        ->addSelect('test_suite', [
            'label'   => 'Suite to run on save',
            'choices' => [
                'my-account-auth' => 'My Account auth forms',
                'product-actions' => 'Floating bar + Buy Now buttons',
                'all'             => 'All theme tests',
            ],
            'default_value' => 'my-account-auth',
            'ui'            => 1,
        ])
        ->addTrueFalse('run_tests_on_save', [
            'label'         => 'Run tests on save',
            'instructions'  => 'Turn ON and save Theme Options to run the selected suite once. This resets to OFF automatically.',
            'ui'            => 1,
            'default_value' => 0,
        ])
    ->endGroup();

return $tests;
