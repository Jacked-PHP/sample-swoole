<?php

namespace MyCode\Http\Controllers\Api;

use Exception;
use League\Plates\Engine;
use MyCode\DB\Models\User;
use MyCode\Rules\RecordExist;
use MyCode\Services\Hash;
use MyCode\Services\Resource;
use MyCode\Services\SessionTable;
use MyCode\Services\Validator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

class UserController
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $data = User::all()->map(function(User $user) {
            $userData = $user->toArray();
            unset($userData['password']);
            unset($userData['created_at']);
            unset($userData['updated_at']);
            return $userData;
        });

        $response->getBody()->write(json_encode(['data' => $data]));
        return $response;
    }

    public function create(RequestInterface $request, ResponseInterface $response)
    {
        $data = json_decode($request->getBody()->getContents(), true);

        try {
            /** @throws Exception */
            $data = $this->validateCreateUserInput(array_merge([
                'name' => null,
                'email' => null,
                'password' => null,
            ], $data));
        } catch (Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Invalid form! ' . $e->getMessage(),
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(422);
        }

        if (isset($data['password'])) {
            $data['password'] = (new Hash)->make($data['password']);
        }

        $resource = new Resource(User::create($data));
        $response->getBody()->write(json_encode($resource));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(201);
    }

    /**
     * @param array $data
     * @return array
     * @throws Exception
     */
    private function validateCreateUserInput(array $data)
    {
        /** @throws Exception */
        Validator::validate($data, [
            'name' => [
                new Required,
                new NotBlank(null, 'User name is requred!'),
                new Type('string', 'User name must be a string!'),
            ],
            'email' => [
                new Required,
                new NotBlank(null, 'User email is required!'),
                new Type('string', 'User email must be a string!'),
                new Email(null, 'User email must be a valid email!'),
            ],
            'password' => [
                new Required,
                new Type('string', 'Password must be string!'),
                new Length([
                    'min' => 5,
                    'max' => 149,
                    'minMessage' => 'The password must be at least {{ limit }} characters long',
                    'maxMessage' => 'The password cannot be longer than {{ limit }} characters',
                ])
            ],
        ]);

        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }

    public function update(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $user_id = isset($args['user_id']) ? (int) $args['user_id'] : null;
        $data = json_decode($request->getBody()->getContents(), true);

        try {
            /** @throws Exception */
            $this->validateUpdateUserInput(array_merge([
                'id' => $user_id,
                'name' => null,
                'password' => null,
            ], $data));
        } catch (Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(422);
        }

        if (isset($data['password'])) {
            $data['password'] = (new Hash)->make($data['password']);
        }

        $user = User::find($user_id);

        if (!$user->update($data)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to update user.',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }

        $response->getBody()->write(json_encode(new Resource($user)));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    private function validateUpdateUserInput(array $data): array
    {
        $rules = [];
        $output = [];

        foreach ($data as $key => $value) {
            $rules[$key] = [
                'id' => [
                    new Required,
                    new Type('integer', 'User id must be an integer.'),
                    new RecordExist(
                        [
                            'model' => User::class,
                            'field' => 'id',
                        ],
                        'User with :id doesn\'t exist!'
                    ),
                ],
                'name' => [
                    new NotBlank(null, 'User name is requred!'),
                    new Type('string', 'User name must be a string!'),
                ],
                'email' => [
                    new IsNull(null, 'Can\'t update user email.'),
                ],
                'password' => [
                    new Type('string', 'Password must be string!'),
                    new Length([
                        'min' => 5,
                        'max' => 149,
                        'minMessage' => 'The password must be at least {{ limit }} characters long',
                        'maxMessage' => 'The password cannot be longer than {{ limit }} characters',
                    ])
                ],
            ][$key];

            $output[$key] = $value;
        }

        /** @throws Exception */
        Validator::validate($data, $rules);

        return $output;
    }

    public function delete(RequestInterface $request, ResponseInterface $response, $args)
    {
        $user_id = isset($args['user_id']) ? (int) $args['user_id'] : null;

        try {
            /** @throws Exception */
            $this->validateDeleteUserInput(['id' => $user_id]);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(422);
        }

        if (!User::find($user_id)->delete()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to delete user.',
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(204);
    }

    private function validateDeleteUserInput(array $data)
    {
        $rules = [];
        $output = [];

        foreach ($data as $key => $value) {
            $rules[$key] = [
                'id' => [
                    new Required,
                    new Type('integer', 'User id must be an integer.'),
                    new RecordExist(
                        [
                            'model' => User::class,
                            'field' => 'id',
                        ],
                        'User with :id doesn\'t exist!'
                    ),
                ],
            ][$key];

            $output[$key] = $value;
        }

        /** @throws Exception */
        Validator::validate($data, $rules);

        return $output;
    }
}