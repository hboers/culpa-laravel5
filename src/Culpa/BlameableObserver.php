<?php

/**
 * Blameable auditing support for Laravel's Eloquent ORM
 * Change Tracking
 * 
 * @author Ross Masters <ross@rossmasters.com>
 * @author Heinrich Boers <mail@heinrich-boers.net>
 * 
 * @copyright Ross Masters 2013
 * @license MIT
 */

namespace Culpa;

use Illuminate\Support\Facades\Config;

class BlameableObserver {

	/**
	 * Creating event
	 * @param \Illuminate\Database\Eloquent\Model $model
	 */
	public function creating ( $model ) {
		$class = get_class ( $model );
		$user_id = $this->activeUser ();
		$new = $model->toArray ();
		foreach ( $new as $key => $value ) {
			if ( $key === 'search_words' ||
				$key === 'created_at' ||
				$key === 'updated_at' ) {
				continue;
			}
			$change = new \Change();
			$change->user_id = $user_id;
			$change->model_id = $model->id;
			$change->model = $class;
			$change->field = $key;
			$change->old_value = '';
			$change->new_value = print_r ( $new[ $key ], true );
			$change->save ();
		}
	}

	/**
	 * Updating event
	 * @param \Illuminate\Database\Eloquent\Model $model
	 */
	public function updating ( $model ) {
		$class = get_class ( $model );
		$user_id = $this->activeUser ();
		$old = $class::find ( $model->id )->toArray ();
		$new = $model->toArray ();
		foreach ( $old as $key => $value ) {
			if ( $key === 'id' ||
				$key === 'search_words' ||
				$key === 'created_at' ||
				$key === 'updated_at' ||
				$value === $new[ $key ] ) {
				continue;
			}
			$change = new \Change();
			$change->user_id = $user_id;
			$change->model_id = $model->id;
			$change->model = $class;
			$change->field = $key;
			$change->old_value = print_r ( $value, true );
			$change->new_value = print_r ( $new[ $key ], true );
			$change->save ();
		}
	}

	/**
	 * Deleting event
	 * @param \Illuminate\Database\Eloquent\Model $model
	 */
	public function deleting ( $model ) {
		$change = new \Change();
		$change->changed_object = get_class ( $model );
		$change->user_id = $this->activeUser ();
		$change->old_value = '';
		$change->new_value = 'deleted';
		$change->save ();
	}

	/**
	 * Update the blameable fields
	 */
	protected function updateChanges ( $model ) {
		$user = $this->activeUser ();

		if ( $user ) {
			// Set updated-by if it has not been touched on this model
			if ( $this->isBlameable ( $model, 'updated' ) && !$model->isDirty ( $this->getColumn ( $model, 'updated' ) ) ) {
				$this->setUpdatedBy ( $model, $user );
			}

			// Set created-by if the model does not exist
			if ( $this->isBlameable ( $model, 'created' ) && !$model->exists && !$model->isDirty ( $this->getColumn ( $model, 'created' ) ) ) {
				$this->setCreatedBy ( $model, $user );
			}
		}
	}

	/**
	 * Update the deletedBy blameable field
	 */
	public function updateDeleteBlameable ( $model ) {
		$user = $this->activeUser ();

		if ( $user ) {
			// Set deleted-at if it has not been touched
			if ( $this->isBlameable ( $model, 'deleted' ) && !$model->isDirty ( $this->getColumn ( $model, 'deleted' ) ) ) {
				$this->setDeletedBy ( $model, $user );
				$model->save ();
			}
		}
	}

	/**
	 * Get the active user
	 *
	 * @return int User ID
	 */
	protected function activeUser () {
		$fn = Config::get ( 'culpa::users.active_user' );
		if ( !is_callable ( $fn ) ) {
			throw new \Exception ( "culpa::users.active_user should be a closure" );
		}

		return $fn ();
	}

}
