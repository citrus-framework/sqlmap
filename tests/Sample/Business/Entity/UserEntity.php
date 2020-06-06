<?php

declare(strict_types=1);

/**
 * @copyright   Copyright 2017, Citrus/besidesplus All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 * @license     http://www.besidesplus.net/
 */

namespace Test\Sample\Business\Entity;

use Citrus\Database\ResultSet\ResultClass;
use Test\Sample\Integration\Property\UserProperty;

class UserEntity extends UserProperty implements ResultClass
{
    /**
     * {@inheritDoc}
     */
    public function bindColumn(): self
    {
        // user_id
        $this->user_id = ($this->user_id * 10);

        return $this;
    }
}
