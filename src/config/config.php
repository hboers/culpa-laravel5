<?php
/**
 * Blameable auditing support for Laravel's Eloquent ORM
 * Use simplified Session auth
 * 
 * @author Ross Masters <ross@rossmasters.com>
 * @author Heinrich Boers <mail@heinrich-boers.net>
 * 
 * @copyright Ross Masters 2013
 * @license MIT
 */


return array(
    'users' => array(
        /**
         * Retrieve the authenticated user's ID
         * @return int|null User ID, or null if not authenticated
         */
        'active_user' => function() {
            return Session::has('auth') ? Session::get('auth')['id'] : null;
        },

        /**
         * Class name of the user object to relate to
         * @var string
         */
        'classname' => 'User',
    ),
);
