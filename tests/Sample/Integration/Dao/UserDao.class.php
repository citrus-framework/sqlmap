<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2019, Citrus All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.besidesplus.net/
 */

namespace Test\Sample\Integration\Dao;

use Citrus\Sqlmap\Faces;

/**
 * ユーザーデータアクセス
 */
class UserDao extends Faces
{
    protected $sqlmap_id = 'Users';
}
