<?php
/*
* This file is part of the scheduler-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\SchedulerBundle\Event;

use Abc\Bundle\SchedulerBundle\Model\ScheduleInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * SchedulerEvent contains an object of type ScheduleInterface
 *
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class SchedulerEvent extends Event
{
    /**
     * @var ScheduleInterface
     */
    private $schedule;

    /**
     * @param ScheduleInterface $schedule
     */
    public function __construct(ScheduleInterface $schedule)
    {
        $this->schedule = $schedule;
    }

    /**
     * @return ScheduleInterface
     */
    public function getSchedule()
    {
        return $this->schedule;
    }
}
