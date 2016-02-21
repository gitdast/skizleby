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
	\Tracy\Debugger::log(Passwords::hash($password));
		$row = $this->connection->query("SELECT * FROM ski_users WHERE username = %s", $username)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

		}
		elseif (!Passwords::verify($password, $row->password)) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

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
		try {
			$this->database->table(self::TABLE_NAME)->insert(array(
				self::COLUMN_NAME => $username,
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
			));
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			throw new DuplicateNameException;
		}
	}
	/*
	public function calculateHash($password){
		return md5($password .'***skihash***');
	}*/
}



//class DuplicateNameException extends \Exception {}
