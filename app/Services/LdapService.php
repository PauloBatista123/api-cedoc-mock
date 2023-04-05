<?php
namespace App\Services;

use Exception;

Class LdapService{

  /**
   * Default Responses.
   *
   * @return void
   */

    public static function connect($user = null, $password = null)
    {
        try {

        $connection = new \LdapRecord\Connection([
            'hosts' => ['10.54.56.8'],
            'base_dn'=> 'DC=aracoop,DC=local',
            'username' => 'ARACOOP\Administrator',
            'password' => 'Araco0p@4264',
            'port' => 389,
        ]);

        $connection->connect();

        $result = $connection->query()->where('samaccountname', '=', $user)->firstOrFail();
        // return ;
        if($connection->auth()->attempt($result['distinguishedname'][0], $password)){
            return [
                'user' => $result['givenname'][0].' '.$result['sn'][0],
                'connect' => true,
            ];
        }else{
            return false;
        }

        } catch (\LdapRecord\Auth\BindException $e) {
            $error = $e->getDetailedError()->getDiagnosticMessage();

            if (strpos($error, '532') !== false) {
                return 'Sua senha está expirada.';
            } elseif (strpos($error, '533') !== false) {
                return 'Sua conta está desabilitada.';
            } elseif (strpos($error, '701') !== false) {
                return 'Sua conta está expirada.';
            } elseif (strpos($error, '775') !== false) {
                return 'Sua conta está bloqueada.';
            }

            return 'Usuário ou senhas incorretos.';
        }

    }

}
