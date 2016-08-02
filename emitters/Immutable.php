<?php namespace nyx\events\emitters;

// Internal dependencies
use nyx\events\interfaces;

/**
 * Immutable Event Emitter
 *
 * An event Emitter that acts as a read-only proxy for an actual event Emitter.
 *
 * @package     Nyx\Events\Emission
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/events/index.html
 */
class Immutable implements interfaces\Emitter
{
    /**
     * @var interfaces\Emitter  The actual event Emitter for which this one acts as a proxy.
     */
    private $emitter;

    /**
     * Constructs an Immutable Emitter as a proxy for an actual Emitter.
     *
     * @param   interfaces\Emitter  $emitter    The actual event Emitter for which this one shall act as a proxy.
     */
    public function __construct(interfaces\Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * {@inheritDoc}
     */
    public function emit($name, $event = self::CREATE_EMPTY_EVENT, ...$arguments) : interfaces\Event
    {
        return $this->emitter->emit($name, $event);
    }

    /**
     * {@inheritDoc}
     */
    public function on(string $name, callable $listener, int $priority = 0) : interfaces\Emitter
    {
        throw new \BadMethodCallException('Immutable event Emitters can not be modified.');
    }

    /**
     * {@inheritDoc}
     */
    public function once(string $name, callable $listener, int $priority = 0) : interfaces\Emitter
    {
        throw new \BadMethodCallException('Immutable event Emitters can not be modified.');
    }

    /**
     * {@inheritDoc}
     */
    public function off(string $name = null, callable $listener = null) : interfaces\Emitter
    {
        throw new \BadMethodCallException('Immutable event Emitters can not be modified.');
    }

    /**
     * {@inheritDoc}
     */
    public function register(interfaces\Subscriber $subscriber) : interfaces\Emitter
    {
        throw new \BadMethodCallException('Immutable event Emitters can not be modified.');
    }

    /**
     * {@inheritDoc}
     */
    public function deregister(interfaces\Subscriber $subscriber) : interfaces\Emitter
    {
        throw new \BadMethodCallException('Immutable event Emitters can not be modified.');
    }

    /**
     * {@inheritDoc}
     */
    public function getListeners(string $name = null) : array
    {
        return $this->emitter->getListeners($name);
    }

    /**
     * {@inheritDoc}
     */
    public function hasListeners(string $name = null) : bool
    {
        return $this->emitter->hasListeners($name);
    }

    /**
     * {@inheritDoc}
     */
    public function countListeners(string $name = null) : int
    {
        return $this->emitter->countListeners($name);
    }
}
