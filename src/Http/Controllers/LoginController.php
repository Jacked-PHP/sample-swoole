<?php

namespace MyCode\Http\Controllers;

use Exception;
use League\Plates\Engine;
use MyCode\DB\Models\User;
use MyCode\Events\UserLogin;
use MyCode\Events\UserLoginFail;
use MyCode\Events\UserLogout;
use MyCode\Helpers\ArrayHelpers;
use MyCode\Rules\RecordExist;
use MyCode\Services\Events;
use MyCode\Services\SessionTable;
use MyCode\Services\Validator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class LoginController
{
    public function login(RequestInterface $request, ResponseInterface $response, $args)
    {
        $templates = new Engine(ROOT_DIR . '/views');
        $response->getBody()->write($templates->render('login', ['message' => '']));
        return $response;
    }

    public function loginHandler(RequestInterface $request, ResponseInterface $response, $args)
    {
        $data = $request->getParsedBody();

        try {
            /** @throws Exception */
            $this->validateLoginForm(ArrayHelpers::only($data, ['email', 'password']));
        } catch (Exception $e) {
            return $response
                ->withHeader('Location', '/login?error=Failed to authenticate!')
                ->withStatus(302);
        }

        $user = User::where('email', $data['email'])->first();

        if (!password_verify($data['password'], $user->password)) {
            Events::dispatch(new UserLoginFail($data['email']));
            return $response
                ->withHeader('Location', '/login?error=Failed to authenticate!')
                ->withStatus(302);
        }

        Events::dispatch(new UserLogin($user));

        $session = $request->getAttribute('session');

        $session_table = SessionTable::getInstance();
        $session_table->set($session['id'], [
            'id' => $session['id'],
            'user_id' => $user->id,
        ]);

        return $response
            ->withHeader('Location', '/admin')
            ->withStatus(302);
    }

    public function logoutHandler(RequestInterface $request, ResponseInterface $response, $args)
    {
        $session = $request->getAttribute('session');

        $session_table = SessionTable::getInstance();
        $session_data = $session_table->get($session['id']);

        Events::dispatch(new UserLogout(User::find($session_data['user_id'])));

        $session_table->set($session['id'], [
            'id' => $session['id'],
        ]);

        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }

    /**
     * @return void
     * @throws Exception
     */
    private function validateLoginForm(array $data): void
    {
        /** @throws Exception */
        Validator::validate($data, [
            'email' => [
                new NotBlank(null, 'User email is required!'),
                new Type('string', 'User email must be a string!'),
                new Email(null, 'User email must be a valid email!'),
                new RecordExist(
                    [
                        'model' => User::class,
                        'field' => 'email',
                    ],
                    'Email is not registered!'
                ),
            ],
            'password' => [
                new NotBlank(null, 'Password is required!'),
                new Type('string', 'Password must be a string!'),
            ],
        ]);
    }
}