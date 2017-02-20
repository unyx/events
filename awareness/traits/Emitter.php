<?php namespace nyx\events\awareness\traits;

// Internal dependencies
use nyx\events\awareness;
use nyx\events\interfaces;

/**
 * Event Emitter Awareness Trait
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
trait Emitter
{
    /**
     * @var interfaces\Emitter  The Event Emitter in use by the exhibitor of this trait.
     */
    private $emitter;

    /**
     * @see \nyx\events\awareness\Emitter::getEmitter()
     */
    public function getEmitter() : ?interfaces\Emitter
    {
        return $this->emitter;
    }

    /**
     * @see \nyx\events\awareness\Emitter::setEmitter()
     */
    public function setEmitter(interfaces\Emitter $emitter) : awareness\Emitter
    {
        $this->emitter = $emitter;

        return $this;
    }

    /**
     * @see \nyx\events\awareness\Emitter::hasEmitter()
     */
    public function hasEmitter() : bool
    {
        return isset($this->emitter);
    }
}
