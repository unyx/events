<?php namespace nyx\events\emitters;

// Internal dependencies
use nyx\events\interfaces;

/**
 * Immutable Event Emitter
 *
 * An event Emitter that acts as a read-only proxy for an actual event Emitter.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
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
    public function emit($event, ...$payload)
    {
        return $this->emitter->emit($event, ...$payload);
    }

    /**
     * {@inheritDoc}
     */
    public function on(string $event, callable $listener, int $priority = 0) : interfaces\Emitter
    {
        throw new \BadMethodCallException('Immutable event Emitters can not be modified.');
    }

    /**
     * {@inheritDoc}
     */
    public function once(string $event, callable $listener, int $priority = 0) : interfaces\Emitter
    {
        throw new \BadMethodCallException('Immutable event Emitters can not be modified.');
    }

    /**
     * {@inheritDoc}
     */
    public function off(string $event = null, callable $listener = null) : interfaces\Emitter
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
    public function getListeners(string $event = null) : array
    {
        return $this->emitter->getListeners($event);
    }

    /**
     * {@inheritDoc}
     */
    public function hasListeners(string $event = null) : bool
    {
        return $this->emitter->hasListeners($event);
    }

    /**
     * {@inheritDoc}
     */
    public function countListeners(string $event = null) : int
    {
        return $this->emitter->countListeners($event);
    }
}
