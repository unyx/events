<?php namespace nyx\events;

/**
 * Event
 *
 * Utility base class for concrete classes acting as events and containing event data.
 *
 * @package     Nyx\Events
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
abstract class Event implements interfaces\Event
{
    /**
     * String constant denoting the type instances of the Event will be of.
     */
    public const TYPE = null;

    /**
     * {@inheritDoc}
     */
    public function getType() : string
    {
        return static::TYPE ?? static::class;
    }
}
