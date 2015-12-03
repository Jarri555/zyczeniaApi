<?php
namespace src\model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class User extends Eloquent {
    public function getUserEmail () {

        return $this->email;
    }

}