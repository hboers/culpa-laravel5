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
use Illuminate\Support\Facades\Auth;

class BlameableObserver {

	function creating ( $model ) 
	{
		if ( !$model->isDirty ( 'created_by' ) ) {
			$model->created_by = Auth::user()->id;
		}
	}

	
	function deleting ( $model ) 
	{
		if ( !$model->isDirty ( 'deleted_by' ) ) {
			$model->deleted_by = Auth::user()->id;
		}
	}

	function updating ( $model ) 
	{
		//$this->changelog($model);
		if ( !$model->isDirty ( 'modified_by' ) ) {
			$model->modified_by = Auth::user()->id;			
		}
	}

	/**
	 * Write Changelog
	 *
	 * @return int User ID
	 */
	 /* TODO implement later, CoC => try: use if App\Changelog class exists
	private function changelog () {
	
		
		
		return Auth::user()->id;
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
		
		
	}*/

}
