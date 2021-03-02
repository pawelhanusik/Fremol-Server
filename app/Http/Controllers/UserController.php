<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return UserResource::collection( User::all() );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return new UserResource($user);
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
            'oldPassword' => 'required'
        ]);

        if (
            password_verify($validated['oldPassword'], request()->user()->password)
        ) {
            $user->update($validated);
            if (request('password') !== null) {
                $user->password = Hash::make(request('password'));
                $user->save();
            }
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
