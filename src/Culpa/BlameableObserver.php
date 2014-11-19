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

	public function creating ( $model ) {
		if ( !$model->isDirty ( 'deleted_by' ) ) {
			$user_id = $this->activeUser ();
			$model->modified_by = $user_id;
		}
	}

	public function deleting ( $model ) {
		if ( !$model->isDirty ( 'deleted_by' ) ) {
			$change->user_id = $this->activeUser ();
			$model->deleted_by = $user_id;
		}
	}

	public function updating ( $model ) {
		$class = get_class ( $model );
		$user_id = $this->activeUser ();
		$old = $class::find ( $model->id )->toArray ();
		$new = $model->toArray ();
		foreach ( $old as $key => $value ) {
			if ( !isset ( $new[ $key ] ) ) {
				continue;
			}
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
		if ( !$model->isDirty ( 'modified_by' ) ) {
			$model->modified_by = $user_id;			
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
