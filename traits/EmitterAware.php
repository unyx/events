<?php namespace nyx\events\traits;

// Internal dependencies
use nyx\events\interfaces;

/**
 * Event Emitter Aware
 *
 * Allows for the implementation of the events\interfaces\EmitterAware interface.
 *
 * @package     Nyx\Events
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
trait EmitterAware
{
    /**
     * @var interfaces\Emitter  The Event Emitter in use by the exhibitor of this trait.
     */
    private $emitter;

    /**
     * @see \nyx\events\interfaces\EmitterAware::getEmitter()
     */
    public function getEmitter() : ?interfaces\Emitter
    {
        return $this->emitter;
    }

    /**
     * @see \nyx\events\interfaces\EmitterAware::setEmitter()
     */
    public function setEmitter(interfaces\Emitter $emitter) : interfaces\EmitterAware
    {
        $this->emitter = $emitter;

        return $this;
    }

    /**
     * @see \nyx\events\interfaces\EmitterAware::hasEmitter()
     */
    public function hasEmitter() : bool
    {
        return isset($this->emitter);
    }
}
