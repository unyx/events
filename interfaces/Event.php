<?php namespace nyx\events\interfaces;

// External dependencies
use nyx\core;

/**
 * Event Interface
 *
 * @package     Nyx\Events
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/events/index.html
 */
interface Event extends core\interfaces\Named, EmitterAware
{
    /**
     * Checks whether no further Event listeners should be invoked for this Event.
     *
     * @return  bool    True if no further Event listeners should be invoked for this Event, false otherwise.
     */
    public function stopped() : bool;

    /**
     * Stops the propagation of the Event to further listeners.
     *
     * @return  $this
     */
    public function stop() : self;
}
