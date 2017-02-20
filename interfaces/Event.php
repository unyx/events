<?php namespace nyx\events\interfaces;

/**
 * Event Interface
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
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
