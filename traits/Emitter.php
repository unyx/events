<?php namespace nyx\events\traits;

// Internal dependencies
use nyx\events\interfaces;

/**
 * Event Emitter
 *
 * Event synchronization point. Registers/removes listeners for events and triggers events. Supports registering
 * listeners by priority and subscribers.
 *
 * Important note: When using this trait, make sure the class you are using it in also implements the Emitter
 * interface.
 *
 * @package     Nyx\Events
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/events/index.html
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
    public function emit($event, ...$data)
    {
        // If an object implementing the Event interface gets passed in as the first argument,
        // we are going to use its name as the trigger and prepend the object itself to the payload.
        if ($event instanceof interfaces\Event) {
            array_unshift($data, $event);
            $event = $event->getType();
        }

        // If there are no listeners for this event, stop further processing.
        if (!isset($this->listeners[$event])) {
            return;
        }

        // Loop through all listeners and invoke the respective callbacks.
        foreach ($this->getListeners($event) as $listener) {
            call_user_func($listener, ...$data);
        }
    }

    /**
     * @see \nyx\events\interfaces\Emitter::on()
     */
    public function on(string $name, callable $listener, int $priority = 0) : interfaces\Emitter
    {
        // Add the listener to the registry.
        $this->listeners[$name][$priority][] = $listener;

        // Make sure we reset the priority chain as this listener might have been added after it was already sorted.
        unset($this->chain[$name]);

        return $this;
    }

    /**
     * @see \nyx\events\interfaces\Emitter::once()
     */
    public function once(string $name, callable $listener, int $priority = 0) : interfaces\Emitter
    {
        // We'll create a wrapper closure which will remove the listener once it receives the first event
        // and pass the arguments to the listener manually.
        $wrapper = function (interfaces\Event $event, ...$params) use (&$wrapper, $name, $listener) {
            $this->off($name, $wrapper);

            call_user_func($listener, $event, ...$params);
        };

        // Register the wrapper.
        return $this->on($name, $wrapper, $priority);
    }

    /**
     * @see \nyx\events\interfaces\Emitter::off()
     */
    public function off(string $name = null, callable $listener = null) : interfaces\Emitter
    {
        // When no listener is specified, we will be removing either all listeners altogether
        // or the listeners for the specified event name.
        if (null === $listener) {
            if (null === $name) {
                $this->listeners = [];
                $this->chain     = [];
            } else {
                unset($this->listeners[$name], $this->chain[$name]);
            }

            return $this;
        }

        // Without a name but with a listener callable we are going to remove the specified
        // listener from all events it's listening to. Do note that this is a costly operation
        // and should be avoided if you can.
        if (null === $name) {

            // First loop through our unsorted event mappings - we don't know the name of the event
            // we might hit in so we need to loop through all bindings.
            foreach ($this->listeners as $event => $priorityMapping) {

                // Now loop through all priority mappings for the given event name.
                foreach ($priorityMapping as $priority => $listeners) {

                    // Finally compare the registered listeners with our passed in listener using
                    // strict equality.
                    if (false !== ($key = array_search($listener, $listeners, true))) {
                        unset($this->listeners[$event][$priority][$key], $this->chain[$event]);
                    }
                }
            }

            return $this;
        }

        // If we get to this point it means we were given both a name and a listener.
        // Make sure the specified event has any listeners registered.
        if (!isset($this->listeners[$name])) {
            return $this;
        }

        // Loop through all listeners attached to the event.
        foreach ($this->listeners[$name] as $priority => $listeners) {
            // Fetch the key of the listener if it exists in the stack, unset it and reset the priority chain
            // for the event.
            if (false !== ($key = array_search($listener, $listeners))) {
                unset($this->listeners[$name][$priority][$key], $this->chain[$name]);
            }
        }

        return $this;
    }

    /**
     * @see \nyx\events\interfaces\Emitter::register()
     */
    public function register(interfaces\Subscriber $subscriber) : interfaces\Emitter
    {
        foreach ($subscriber->getSubscribedEvents() as $name => $params) {
            // If just a callable was given.
            if (is_string($params)) {
                $this->on($name, [$subscriber, $params]);
            }
            // A callable and a priority.
            elseif (isset($params[0]) && is_string($params[0])) {
                $this->on($name, [$subscriber, $params[0]], isset($params[1]) ? $params[1] : 0);
            }
            // An array of callables (and their optional priorities)
            else {
                foreach ($params as $listener) {
                    $this->on($name, [$subscriber, $listener[0]], isset($listener[1]) ? $listener[1] : 0);
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
        foreach ($subscriber->getSubscribedEvents() as $name => $params) {
            if (is_array($params) && is_array($params[0])) {
                foreach ($params as $listener) {
                    $this->off($name, [$subscriber, $listener[0]]);
                }
            } else {
                $this->off($name, [$subscriber, is_string($params) ? $params : $params[0]]);
            }
        }

        return $this;
    }

    /**
     * @see \nyx\events\interfaces\Emitter::getListeners()
     */
    public function getListeners(string $name = null) : array
    {
        // Sort the listeners for a given trigger name and return that subset.
        if (isset($name)) {
            if (!isset($this->chain[$name])) {
                $this->sortListeners($name);
            }

            return $this->chain[$name];
        }

        // If no trigger name was given, sort all listeners and return them.
        foreach (array_keys($this->listeners) as $name) {
            if (!isset($this->chain[$name])) {
                $this->sortListeners($name);
            }
        }

        return $this->chain;
    }

    /**
     * @see \nyx\events\interfaces\Emitter::hasListeners()
     */
    public function hasListeners(string $name = null) : bool
    {
        return (bool) $this->countListeners($name);
    }

    /**
     * @see \nyx\events\interfaces\Emitter::countListeners()
     */
    public function countListeners(string $name = null) : int
    {
        return count($this->getListeners($name));
    }

    /**
     * Sorts the listeners for the given event name descending by priority, so the higher priority listeners
     * can get called first in the chain.
     *
     * @param   string  $name The name of the event.
     */
    protected function sortListeners(string $name)
    {
        // Only prepare the chain when the actual event has any listeners attached.
        if (!isset($this->listeners[$name])) {
            return;
        }

        $this->chain[$name] = [];

        // Sort the listeners by priority in a descending order.
        krsort($this->listeners[$name]);

        $this->chain[$name] = array_merge(...$this->listeners[$name]);
    }
}
