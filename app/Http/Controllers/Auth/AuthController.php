<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Session\SessionManager;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Gestion\UserGestion;

class AuthController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Registration & Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users, as well as the
	| authentication of existing users. By default, this controller uses
	| a simple trait to add these behaviors. Why don't you explore it?
	|
	*/

	use AuthenticatesAndRegistersUsers;

	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
		$this->middleware('guest', ['except' => 'getLogout']);
	}

	/**
	 * Handle a login request to the application.
	 *
	 * @param  App\Http\Requests\LoginRequest  $request
	 * @param  Illuminate\Session\SessionManager  $session
	 * @return Response
	 */
	public function postLogin(
		LoginRequest $request,
		SessionManager $session)
	{
		// Vérification pot de miel
		if($request->get('user') != '') return redirect('/');

		$credentials = $request->only('email', 'password');

		if ($this->auth->attempt($credentials, $request->has('souvenir')))
		{
			$session->put('statut', $this->auth->user()->role->slug);
			return redirect('/');
		}

		return redirect('/auth/login')
		->with('error', trans('front/login.credentials'))
		->withInput($request->only('email'));
	}

	/**
	 * Log the user out of the application.
	 *
	 * @param  Illuminate\Session\SessionManager  $session
	 * @return Response
	 */
	public function getLogout(
		SessionManager $session)
	{
		$this->auth->logout();
		$session->put('statut', 'visitor');
		return redirect('/');
	}

	/**
	 * Handle a registration request for the application.
	 *
	 * @param  App\Http\Requests\RegisterRequest  $request
	 * @param  App\Gestion\UserGestion $user_gestion
	 * @param  Illuminate\Session\SessionManager  $session
	 * @return Response
	 */
	public function postRegister(
		RegisterRequest $request,
		UserGestion $user_gestion,
		SessionManager $session)
	{
		// Vérification pot de miel
		if($request->get('user') != '') return redirect('/');

		$user = $user_gestion->store($request->all());

		$this->auth->login($user);
		$session->put('statut', 'visitor');

		return redirect('/')->with('ok', trans('front/register.ok'));
	}

}