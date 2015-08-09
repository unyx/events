<?php namespace nyx\events\traits;

// Internal dependencies
use nyx\events\interfaces;

/**
 * Event Emitter Aware
 *
 * Allows for the implementation of the interfaces\EmitterAware interface.
 *
 * @package     Nyx\Events
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/events/index.html
 */
trait EmitterAware
{
    /**
     * @var interfaces\Emitter  The Event Emitter in use by the exhibitor of this trait.
     */
    private $emitter;

    /**
     * @see interfaces\EmitterAware::getEmitter()
     */
    public function getEmitter() : interfaces\Emitter
    {
        return $this->emitter;
    }

    /**
     * @see interfaces\EmitterAware::setEmitter()
     */
    public function setEmitter(interfaces\Emitter $emitter) : self
    {
        $this->emitter = $emitter;

        return $this;
    }

    /**
     * @see interfaces\EmitterAware::hasEmitter()
     */
    public function hasEmitter() : bool
    {
        return null !== $this->emitter;
    }
}
