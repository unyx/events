<?php namespace nyx\events\traits;

// Internal dependencies
use nyx\events\interfaces;
use nyx\events;

/**
 * Event Emitter
 *
 * Event synchronization point. Registers/removes listeners for events and triggers events. Supports registering
 * listeners by priority and subscribers.
 *
 * Important note: When using this trait, make sure the class you are using it in also implements the Emitter
 * interface to allow for proper compatibility with Subscribers etc.
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
     * @see events\interfaces\Emitter::emit()
     */
    public function emit($name, $event = interfaces\Emitter::CREATE_EMPTY_EVENT, ...$arguments) : interfaces\Event
    {
        // If we only get an Event object, let's do some magic.
        if ($name instanceof interfaces\Event) {
            if (!$event instanceof interfaces\Event) {
                // Since $event wasn't actually an Event instance, it means it's data to pass to the listeners...
                // unless it's our little magic constant.
                if ($event !== interfaces\Emitter::CREATE_EMPTY_EVENT) {
                    array_unshift($arguments, $event);
                }

                // Well, technically someone *could* pass two different Event objects to use the triggerName of the first
                // one but forward the second one... beats me why one would do that, though.
                $event = $name;
            }

            // Make sure we've got a triggerName.
            if (!$name = $name->getName()) {
                throw new \LogicException("The given Event does not have a valid name.");
            }
        }
        // Make sure we've got an Event instance, so create one if necessary.
        elseif (!$event instanceof interfaces\Event) {
            // Since $event wasn't actually an Event instance, it means it's data to pass to the listeners...
            // unless it's our little magic constant.
            if ($event !== interfaces\Emitter::CREATE_EMPTY_EVENT) {
                array_unshift($arguments, $event);
            }

            $event = new events\Event($name);
        } else {
            // Ensure the Event knows its trigger. This will be a duplicate call for passed Events that had their names
            // already set but can't really be avoided (the overhead of workarounds is bigger than that of just
            // calling the method).
            $event->setName($name);
        }

        // Keep track of which Emitter is handling the Event.
        $event->setEmitter($this);

        // If there are no listeners for this event, just return the Event instance.
        if (!isset($this->listeners[$name])) {
            return $event;
        }

        // Time to prepare the arguments which are going to be passed to the listeners. We need the Event instance
        // to be first in all cases, so let's start with that.
        array_unshift($arguments, $event);

        // Now perform the actual emitting. Loop through the listeners and invoke the respective callables.
        foreach ($this->getListeners($name) as $listener) {
            call_user_func_array($listener, $arguments);

            // Break further propagation if the event has been put to a halt.
            if ($event->stopped()) {
                break;
            }
        }

        return $event;
    }

    /**
     * @see events\interfaces\Emitter::on()
     */
    public function on(string $name, callable $listener, int $priority = 0) : self
    {
        // Add the listener to the registry.
        $this->listeners[$name][$priority][] = $listener;

        // Make sure we reset the priority chain as this listener might have been added after it was already sorted.
        unset($this->chain[$name]);

        return $this;
    }

    /**
     * @see events\interfaces\Emitter::once()
     */
    public function once(string $name, callable $listener, int $priority = 0) : self
    {
        // We'll create a wrapper closure which will remove the listener once it receives the first event
        // and pass the arguments to the listener manually.
        $wrapper = function () use (&$wrapper, $name, $listener) {
            $this->off($name, $wrapper);

            call_user_func_array($listener, func_get_args());
        };

        // Register the wrapper.
        return $this->on($name, $wrapper, $priority);
    }

    /**
     * @see events\interfaces\Emitter::off()
     */
    public function off(string $name = null, callable $listener = null) : self
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
     * @see events\interfaces\Emitter::subscribe()
     */
    public function register(interfaces\Subscriber $subscriber) : self
    {
        foreach ($subscriber->getSubscribedEvents() as $name => $params) {
            // If just a callable was given.
            if (is_string($params)) {
                $this->on($name, [$subscriber, $params]);
            }
            // A callable and a priority.
            elseif (isset($params[0]) and is_string($params[0])) {
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
     * @see events\interfaces\Emitter::unsubscribe()
     */
    public function deregister(interfaces\Subscriber $subscriber) : self
    {
        foreach ($subscriber->getSubscribedEvents() as $name => $params) {
            if (is_array($params) and is_array($params[0])) {
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
     * @see events\interfaces\Emitter::getListeners()
     */
    public function getListeners(string $name = null) : array
    {
        // Sort the listeners for a given trigger name and return that subset.
        if (null !== $name) {
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
     * @see events\interfaces\Emitter::hasListeners()
     */
    public function hasListeners(string $name = null) : bool
    {
        return (bool) $this->countListeners($name);
    }

    /**
     * @see events\interfaces\Emitter::countListeners()
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
    protected function sortListeners($name)
    {
        // Only prepare the chain when the actual event has any listeners attached.
        if (isset($this->listeners[$name])) {
            $this->chain[$name] = [];

            // Sort the listeners by priority in a descending order.
            krsort($this->listeners[$name]);

            $this->chain[$name] = call_user_func_array('array_merge', $this->listeners[$name]);
        }
    }
}
