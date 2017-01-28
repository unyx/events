<?php namespace nyx\events\interfaces;

/**
 * Event Interface
 *
 * @package     Nyx\Events
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        Decide: Reintroduce propagation halting?
 */
interface Event
{
    /**
     * Returns the type of this Event.
     *
     * @return  string
     */
    public function getType() : string;
}
