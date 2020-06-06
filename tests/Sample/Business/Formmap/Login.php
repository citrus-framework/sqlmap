<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, Citrus All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.besidesplus.net/
 */

use Citrus\Authentication\Item;
use Citrus\Formmap\ElementType;

return [
    'Login' => [
        'login' => [
            'class' => Item::class,
            'elements' => [
                'user_id' => [
                    'form_type' => ElementType::FORM_TYPE_TEXT,
                    'property'  => 'user_id',
                    'name'      => 'ユーザID',
                    'var_type'  => ElementType::VAR_TYPE_STRING,
                ],
                'password' => [
                    'form_type' => ElementType::FORM_TYPE_PASSWD,
                    'property'  => 'password',
                    'name'      => 'パスワード',
                    'var_type'  => ElementType::VAR_TYPE_STRING,
                ],
                'btn_login' => [
                    'form_type' => ElementType::FORM_TYPE_SUBMIT,
                    'name'      => 'ログイン',
                ],
            ],
        ],
    ],
];
