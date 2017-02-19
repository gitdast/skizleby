<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;

class UserAuthentication extends Nette\Object implements Nette\Security\IAuthenticator {
	const
		TABLE_NAME = 'ski_users',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'username',
		COLUMN_PASSWORD_HASH = 'password',
		COLUMN_ROLE = 'role',
		SLT = '***skihash***skihash***';

	/** @var \DibiConnection */
	private $connection;

	public function __construct(\DibiConnection $connection){
		$this->connection = $connection;
	}

	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials) {
		list($username, $password) = $credentials;
		//\Tracy\Debugger::log(Passwords::hash($password));
		$row = $this->connection->query("SELECT * FROM ski_users WHERE username = %s", $username)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('Špatné jméno.', self::IDENTITY_NOT_FOUND);

		}
		elseif (!Passwords::verify($password, $row->password)) {
			throw new Nette\Security\AuthenticationException('Špatné heslo.', self::INVALID_CREDENTIAL);

		}
		elseif (Passwords::needsRehash($row->password)) {
			//$row->update(array(self::COLUMN_PASSWORD_HASH => Passwords::hash($password)));
			\Tracy\Debugger::log("rehash");
		}

		unset($row->password);
		return new Nette\Security\Identity($row->id, $row->role, (array) $row);
		
	}

	/**
	 * Adds new user.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function add($username, $password) {
		$this->connection->query("INSERT INTO ski_users", ['username' => $username, 'password' => Passwords::hash($password)]);
	}
	
	public function changePassword($values, $user){
		$row = $this->connection->query("SELECT * FROM ski_users WHERE id = %i", $user->getId())->fetch();
		
		if(!Passwords::verify($values->oldpassword, $row->password)){
			throw new Nette\Security\AuthenticationException('Špatné heslo.', self::INVALID_CREDENTIAL);
		}
		
		$this->connection->query("UPDATE ski_users SET password = %s WHERE id = %i", Passwords::hash($values->password), $user->getId());
	}

}



//class DuplicateNameException extends \Exception {}
