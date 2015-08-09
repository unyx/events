<?php namespace nyx\events;

/**
 * Event Emitter
 *
 * Concrete use of the Emitter trait {@see traits\Emitter}, made available in case you need to keep an Emitter
 * as a separate object.
 *
 * @package     Nyx\Events
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2016 Nyx Dev Team
 * @link        http://docs.muyo.io/nyx/events/index.html
 */
class Emitter implements interfaces\Emitter
{
    use traits\Emitter;
}
