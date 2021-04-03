<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize(User::class);
        
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
        $this->authorize(User::class);

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
            if (request()->has('avatar')) {
                request()->validate([
                    'avatar' => 'required|image|mimetypes:image/png,image/jpeg|max:2048'
                ]);
                $avatarDstPath = $user->id;
                if (Storage::exists($avatarDstPath)) {
                    Storage::delete($avatarDstPath);
                }
                $avatar_url = request()->file('avatar')->storeAs('public/avatars', $avatarDstPath);
                $user->avatar_url = $avatar_url;
                $user->save();
            }
            
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
