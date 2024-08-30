<?php

namespace App\Transformers;

class RandomUserTransformer
{
    public function transform(array $data): array
    {
        return [
            'first_name' => $data['name']['first'],
            'last_name'  => $data['name']['last'],
            'email'      => $data['email'],
            'username'   => $data['login']['username'],
            'gender'     => $data['gender'],
            'country'    => $data['location']['country'],
            'city'       => $data['location']['city'],
            'phone'      => $data['phone'],
            'password'   => md5($data['login']['password']),
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ];
    }
}
