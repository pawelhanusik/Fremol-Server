<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(User $user)
    {
        $this->authorize($user);

        $validated = request()->validate([
            'email' => 'required|email',
            'name' => 'required',
            'password' => 'required',
            'oldPassword' => 'required'
        ]);

        if (
            auth()->once([
                'name' => request()->user()->name,
                'password' => $validated['oldPassword']
            ])
        ) {
            $user->update($validated);
            return null;
        } else {
            abort(401, "Invalid credentials");
            return null;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $this->authorize($user);

        $user->delete();
        return null;
    }
}
