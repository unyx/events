<?php namespace nyx\events\interfaces;

/**
 * Event Emitter Aware Interface
 *
 * An Event Emitter Aware object is one that may contain an Emitter which can be injected and retrieved using the
 * respective getters/setters.
 *
 * @package     Nyx\Events\Emission
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/events/emission.html
 */
interface EmitterAware
{
    /**
     * Returns the Event Emitter instance in use by the implementer.
     *
     * @return  Emitter
     */
    public function getEmitter();

    /**
     * Sets an Event Emitter instance inside the implementer.
     *
     * @param   Emitter  $emitter   The Emitter to set.
     * @return  $this
     */
    public function setEmitter(Emitter $emitter);

    /**
     * Checks whether the implementer has a set Event Emitter instance.
     *
     * @return  bool    True when an Event Emitter is set, false otherwise.
     */
    public function hasEmitter();
}
