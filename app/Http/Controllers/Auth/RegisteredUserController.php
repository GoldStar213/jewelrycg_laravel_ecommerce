<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9]+$/u'], 
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $notification = new Notification();
        $notification->message = Config::get('constants.strings.ask_verify');
        $notification->user_id = $user->id;
        $notification->link = "/verify-email";
        $notification->save();

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->stateless()->user();

        // Check if the user exists in your database
        $existingUser = User::where('email', $user->getEmail())->first();

        if ($existingUser) {
            // Log in the user
            auth()->login($existingUser, true);
        } else {
            // Create a new user account
            $newUser = User::create([
                'first_name' => $user->user['given_name'],
                'last_name' => $user->user['family_name'],
                'username' => null,
                'email' => $user->getEmail(),
                'password' => Hash::make(Str::random(10)),
                // Add any other required fields
            ]);
            $notification = new Notification();
            $notification->message = Config::get('constants.strings.ask_verify');
            $notification->user_id = $user->id;
            $notification->link = "/verify-email";
            $notification->save();

            // Log in the new user
            auth()->login($newUser, true);
        }

        // Redirect to the desired page
        return redirect('/dashboard');
    }

    public function redirectToApple()
    {
        return Socialite::driver('apple')->redirect();
    }

    public function handleAppleCallback()
    {
        $user = Socialite::driver('apple')->user();

        // Check if the user exists in your database
        $existingUser = User::where('email', $user->getEmail())->first();

        if ($existingUser) {
            // Log in the user
            auth()->login($existingUser, true);
        } else {
            // Create a new user account
            $newUser = User::create([
                'first_name' => $user->user['given_name'],
                'last_name' => $user->user['family_name'],
                'username' => null,
                'email' => $user->getEmail(),
                'password' => Hash::make(Str::random(10)),
                // Add any other required fields
            ]);

            $notification = new Notification();
            $notification->message = Config::get('constants.strings.ask_verify');
            $notification->user_id = $user->id;
            $notification->link = "/verify-email";
            $notification->save();
            // Log in the new user
            auth()->login($newUser, true);
        }

        // Redirect to the desired page
        return redirect('/dashboard');
    }
}