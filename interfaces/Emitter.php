<?php namespace nyx\events\interfaces;

/**
 * Event Emitter Interface
 *
 * @package     Nyx\Events
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 * @todo        Decide: Optionally append the Emitter instance and event type to the payload when emitting?
 * @todo        Decide: Event propagation stopping?
 * @todo        Decide: Wildcard events / channel support?
 */
interface Emitter
{
    /**
     * Triggers the listeners of a given event.
     *
     * @param   string|Event    $event          The name of the event to emit or an object implementing the Event
     *                                          interface, whose name will be used.
     * @param   mixed           ...$payload     The data to pass to listeners, in the order given. If $event is an
     *                                          instance of the Event interface, it will be prepended to the $payload.
     */
    public function emit($event, ...$payload);

    /**
     * Adds an event listener that listens to the specified event.
     *
     * @param   string   $event             The event to listen for.
     * @param   callable $listener          The listener to add.
     * @param   integer  $priority          The higher this value, the earlier an event listener will be triggered.
     * @return  $this
     */
    public function on(string $event, callable $listener, int $priority = 0) : Emitter;

    /**
     * Adds an event listener that listens to the specified event but only gets fired once and then removed from
     * the stack.
     *
     * @param   string      $event          The event to listen for.
     * @param   callable    $listener       The listener to add.
     * @param   integer     $priority       The higher this value, the earlier an event listener will be triggered
     *                                      in the chain.
     * @return  $this
     */
    public function once(string $event, callable $listener, int $priority = 0) : Emitter;

    /**
     * Removes the specified listener from the stack of a given event, OR
     * removes all listeners for a given event name when only $listener is null, OR
     * removes the given listener from all events it's listening to when only $name is null, OR
     * removes all listeners registered in this Emitter if both $name and $listener are null,
     *
     * @param   string      $event      The event to remove a listener from.
     * @param   callable    $listener   The listener to remove.
     * @return  $this
     */
    public function off(string $event = null, callable $listener = null) : Emitter;

    /**
     * Registers an Event Subscriber with this Emitter. The Subscriber is queried for a list of events
     * he is interested in and afterwards registered as a listener for them.
     *
     * @param   Subscriber  $subscriber
     * @return  $this
     */
    public function register(Subscriber $subscriber) : Emitter;

    /**
     * Deregisters an Event Subscriber from this Emitter, removing all listeners it has registered
     * with.
     *
     * @param   Subscriber  $subscriber
     * @return  $this
     */
    public function deregister(Subscriber $subscriber) : Emitter;

    /**
     * Returns an array containing the priority-sorted listeners for the given event or for all events if no trigger
     * name is given.
     *
     * @param   string  $name   The trigger name of the event.
     * @return  array           The event listeners for the specified event.
     */
    public function getListeners(string $name = null) : array;

    /**
     * Checks whether the given trigger name has any listeners registered or when no trigger name is given whether
     * any listeners at all are registered.
     *
     * @param   string  $name   The trigger name of the event.
     * @return  bool
     */
    public function hasListeners(string $name = null) : bool;

    /**
     * Returns the count of the listeners for the given trigger name or the count of all registered listeners if
     * no trigger name is given.
     *
     * @param   string  $name   The trigger name of the event.
     * @return  int
     */
    public function countListeners(string $name = null) : int;
}
