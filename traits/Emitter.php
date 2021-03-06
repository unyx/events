<?php namespace nyx\events\traits;

// Internal dependencies
use nyx\events\interfaces;

/**
 * Event Emitter
 *
 * Important note: When using this trait, make sure the class you are using it in also implements the Emitter
 * interface.
 *
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
trait Emitter
{
    /**
     * @var array   The registered listeners.
     */
    private $listeners = [];

    /**
     * @var array   The priority-sorted chain of listeners.
     */
    private $chain = [];

    /**
     * @see \nyx\events\interfaces\Emitter::emit()
     */
    public function emit($event, ...$payload)
    {
        // If an object implementing the Event interface gets passed in as the first argument,
        // we are going to use its name as the trigger and prepend the object itself to the payload.
        if ($event instanceof interfaces\Event) {
            array_unshift($payload, $event);
            $event = $event->getType();
        }

        // If there are no listeners for this event, stop further processing.
        if (!isset($this->listeners[$event])) {
            return;
        }

        // Loop through all listeners and invoke the respective callbacks.
        foreach ($this->getListeners($event) as $listener) {
            $listener(...$payload);
        }
    }

    /**
     * @see \nyx\events\interfaces\Emitter::on()
     */
    public function on(string $event, callable $listener, int $priority = 0) : interfaces\Emitter
    {
        // Register the listener for the given event.
        $this->listeners[$event][$priority][] = $listener;

        // Make sure we reset the priority chain as this listener might have been added after it
        // had already been sorted.
        unset($this->chain[$event]);

        return $this;
    }

    /**
     * @see \nyx\events\interfaces\Emitter::once()
     */
    public function once(string $event, callable $listener, int $priority = 0) : interfaces\Emitter
    {
        // We'll create a wrapper closure which will remove the listener once it receives the first event
        // and forward the arguments from the wrapper to the actual listener.
        $wrapper = function (...$payload) use (&$wrapper, $event, $listener) {
            $this->off($event, $wrapper);

            $listener(...$payload);
        };

        // Register the wrapper.
        return $this->on($event, $wrapper, $priority);
    }

    /**
     * @see \nyx\events\interfaces\Emitter::off()
     */
    public function off(string $event = null, callable $listener = null) : interfaces\Emitter
    {
        // When no listener is specified, we will be removing either all listeners altogether
        // or the listeners for the specified event name.
        if (!isset($listener)) {
            if (isset($event)) {
                unset($this->listeners[$event], $this->chain[$event]);
            } else {
                $this->listeners = [];
                $this->chain     = [];
            }

            return $this;
        }

        // Without a name but with a listener callable we are going to remove the specified listener from
        // all events it's listening to. Note that this is a costly operation and should be avoided if you can.
        if (!isset($event)) {
            return $this->removeListener($listener);
        }

        // If we get to this point it means we were given both a name and a listener.
        return $this->removeListenerFromEvent($listener, $event);
    }

    /**
     * @see \nyx\events\interfaces\Emitter::register()
     */
    public function register(interfaces\Subscriber $subscriber) : interfaces\Emitter
    {
        foreach ($subscriber->getSubscribedEvents() as $event => $params) {
            // If just a callable was given.
            if (is_string($params)) {
                $this->on($event, [$subscriber, $params]);
            }
            // A callable and a priority.
            elseif (isset($params[0]) && is_string($params[0])) {
                $this->on($event, [$subscriber, $params[0]], isset($params[1]) ? $params[1] : 0);
            }
            // An array of callables (and their optional priorities)
            else {
                foreach ($params as $listener) {
                    $this->on($event, [$subscriber, $listener[0]], isset($listener[1]) ? $listener[1] : 0);
                }
            }
        }

        return $this;
    }

    /**
     * @see \nyx\events\interfaces\Emitter::deregister()
     */
    public function deregister(interfaces\Subscriber $subscriber) : interfaces\Emitter
    {
        foreach ($subscriber->getSubscribedEvents() as $event => $params) {
            if (is_array($params) && is_array($params[0])) {
                foreach ($params as $listener) {
                    $this->off($event, [$subscriber, $listener[0]]);
                }
            } else {
                $this->off($event, [$subscriber, is_string($params) ? $params : $params[0]]);
            }
        }

        return $this;
    }

    /**
     * @see \nyx\events\interfaces\Emitter::getListeners()
     */
    public function getListeners(string $event = null) : array
    {
        // Sort the listeners for a given trigger name and return that subset.
        if (isset($event)) {
            if (!isset($this->chain[$event])) {
                $this->sortListeners($event);
            }

            return $this->chain[$event];
        }

        // If no trigger name was given, sort all listeners and return them.
        foreach (array_keys($this->listeners) as $event) {
            if (!isset($this->chain[$event])) {
                $this->sortListeners($event);
            }
        }

        return $this->chain;
    }

    /**
     * @see \nyx\events\interfaces\Emitter::hasListeners()
     */
    public function hasListeners(string $event = null) : bool
    {
        return count($this->getListeners($event)) > 0;
    }

    /**
     * @see \nyx\events\interfaces\Emitter::countListeners()
     */
    public function countListeners(string $event = null) : int
    {
        return count($this->getListeners($event));
    }

    /**
     * Removes the given listener from all events it is listening to.
     *
     * @param   callable            $listener   The listener to remove.
     * @return  $this
     */
    protected function removeListener(callable $listener) : interfaces\Emitter
    {
        foreach ($this->listeners as $event => $priorityMap) {
            foreach ($priorityMap as $priority => $listeners) {
                if (false !== $key = array_search($listener, $listeners, true)) {
                    unset($this->listeners[$event][$priority][$key], $this->chain[$event]);
                }
            }
        }

        return $this;
    }

    /**
     * Removes the given listener from the given event.
     *
     * @param   callable            $listener   The listener to remove.
     * @param   string              $event      The event to remove the listener from.
     * @return  $this
     */
    protected function removeListenerFromEvent(callable $listener, string $event) : interfaces\Emitter
    {
        foreach ($this->listeners[$event] as $priority => $listeners) {
            if (false !== $key = array_search($listener, $listeners, true)) {
                unset($this->listeners[$event][$priority][$key], $this->chain[$event]);
            }
        }

        return $this;
    }

    /**
     * Sorts the listeners for the given event name descending by priority, so the higher priority listeners
     * can get called first in the chain.
     *
     * @param   string  $event  The name of the event.
     * @return  $this
     */
    protected function sortListeners(string $event) : interfaces\Emitter
    {
        if (!isset($this->listeners[$event])) {
            return $this;
        }

        // Sort the listeners by priority in a descending order.
        krsort($this->listeners[$event]);

        $this->chain[$event] = array_merge(...$this->listeners[$event]);

        return $this;
    }
}
