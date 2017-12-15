<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\DataGrid\Tests\Fixtures;

class EventManagerMock
{
    protected $listeners;

    public function __construct($listeners)
    {
        $this->listeners = $listeners;
    }

    public function getListeners()
    {
        return [$this->listeners];
    }
}
