<?php namespace nyx\events\interfaces;

/**
 * Event Subscriber Interface
 *
 * A Subscriber defines the events it wants to listen to on its own. Once registered with a Emitter,
 * the Emitter queries the Subscriber for those events and registers it as a listener for them.
 *
 * @package     Nyx\Events
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/events/index.html
 */
interface Subscriber
{
    /**
     * Returns an array of event names this subscriber wants to listen to. The returned array can have either of the
     * following structures:
     *
     *   ['eventName' => 'methodName']
     *   ['eventName' => ['methodName', $priority]]
     *   ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return  array   The event names to listen for.
     */
    public function getSubscribedEvents();
}
