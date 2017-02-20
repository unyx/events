<?php namespace nyx\events\awareness;

// Internal dependencies
use nyx\events\interfaces;

/**
 * Event Emitter Aware Interface
 *
 * An Event Emitter Aware object is one that may have access to an Emitter, which can be injected and
 * retrieved using the respective getters and setters.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Emitter
{
    /**
     * Returns the Event Emitter instance this object is using, if any.
     *
     * @return  interfaces\Emitter
     */
    public function getEmitter() : ?interfaces\Emitter;

    /**
     * Sets the Event Emitter instance this object should have access to.
     *
     * @param   interfaces\Emitter  $emitter    The Emitter to set.
     * @return  $this
     */
    public function setEmitter(interfaces\Emitter $emitter) : Emitter;

    /**
     * Checks whether the object has access to an Event Emitter instance.
     *
     * @return  bool    True when an Event Emitter is set, false otherwise.
     */
    public function hasEmitter() : bool;
}
