<?php
/**
 * Blameable auditing support for Laravel's Eloquent ORM
 *
 * @author Ross Masters <ross@rossmasters.com>
 * @copyright Ross Masters 2013
 * @license MIT
 */

namespace Culpa;

use Illuminate\Support\Facades\Config;

/**
 * Add event-triggered references to the authorised user that triggered them
 *
 * @property \Illuminate\Database\Eloquent\Model $updated_by The updater of this model
 * @property int $updated_by_id User id of the model updater
 */
trait UpdatedBy
{
    /**
     * Get the user that updated the model
     * @return \Illuminate\Database\Eloquent\Model User instance
     */
    function updatedBy()
    {
        return $this->belongsTo('App\User','updated_by')->withTrashed();
    }
}
