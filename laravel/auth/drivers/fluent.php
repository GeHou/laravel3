<?php namespace Laravel\Auth\Drivers;

use Laravel\Hash;
use Laravel\Config;
use Laravel\Database as DB;

class Fluent extends Driver {

	/**
	 * Get the current user of the application.
	 *
	 * If the user is a guest, null should be returned.
	 *
	 * @param  int  $id
	 * @return mixed|null
	 */
	public function retrieve($id)
	{
		if (filter_var($id, FILTER_VALIDATE_INT) !== false)
		{
			return DB::table(Config::get('auth.table'))->where(Config::get('auth.id'), '=', $id)->first();
			// return DB::table(Config::get('auth.table'))->find($id);
		}
	}

	/**
	 * Attempt to log a user into the application.
	 *
	 * @param  array $arguments
	 * @return void
	 */
	public function attempt($arguments = array())
	{
		$user = $this->get_user($arguments);

		// hou fix bug
 		if(is_array($user)) {
 			$user = (object) $user;
 		}

		// If the credentials match what is in the database we will just
		// log the user into the application and remember them if asked.
		$password = $arguments['password'];

		$password_field = Config::get('auth.password', 'password');
		// hou fix bug
		$id_field = Config::get('auth.id', 'id');

		if ( ! is_null($user) and Hash::check($password, $user->{$password_field}))
		{
			return $this->login($user->{$id_field}, array_get($arguments, 'remember'));
		}

		return false;
	}

	/**
	 * Get the user from the database table.
	 *
	 * @param  array  $arguments
	 * @return mixed
	 */
	protected function get_user($arguments)
	{
		$table = Config::get('auth.table');

		return DB::table($table)->where(function($query) use($arguments)
		{
			$username = Config::get('auth.username');
			
			$query->where($username, '=', $arguments['username']);

			foreach(array_except($arguments, array('username', 'password', 'remember')) as $column => $val)
			{
			    $query->where($column, '=', $val);
			}
		})->first();
	}

}
