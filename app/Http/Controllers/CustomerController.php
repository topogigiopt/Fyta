<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class CustomerController extends ProfileController
{
    public function render()
    {
        return view('pages.profile', ['layout' => ['scripts' => ['js/profile_page.js'], 'styles' => ['css/profile_page.css', 'css/registerpage.css', 'css/homepage.css']]]);
    }

    public function wishlist()
    {
        $role = User::checkUser();
        if ($role == User::$GUEST) {
            return response()->json(['message' => 'You must login to access your wishlist'], 401);
        } else if ($role == User::$MANAGER)
            return response()->json(['message' => 'Managers can\'t access wishlist'], 403);

        $user = Auth::user();
        $wishlist = $user->wishlists()->first(); //TODO: integrate multiple wishlists
        if ($wishlist == null) {
            return [];
        }
        $products = $wishlist->products()->get()->all();
        $items = array_map(function ($product) {
            $data = ['name' => $product->name, 'price' => $product->price, 'id' => $product->id];
            $img = $product->images()->first();
            $data['img'] = 'img/' . $img->img_name;
            return $data;
        }, $products);

        return $items;
    }

    public function addProductToWishlist($id)
    {
        $role = User::checkUser();
        if ($role == User::$GUEST) {
            return response()->json(['message' => 'You must login to add items to your wishlist'], 401);
        } else if ($role == User::$MANAGER)
            return response()->json(['message' => 'Managers can\'t add items to wishlists'], 403);


        $user = Auth::user();
        $wishlist = $user->wishlists()->first();
        if ($wishlist == null) {
            return [];
        }

        $wishlist->products()->attach($id);


        return response('Product added to wishlist');
    }

    public function removeProductFromWishlist($id)
    {
        $role = User::checkUser();
        if ($role == User::$GUEST) {
            return response()->json(['message' => 'You must login to add items to your wishlist'], 401);
        } else if ($role == User::$MANAGER)
            return response()->json(['message' => 'Managers can\'t add items to wishlists'], 403);


        $user = Auth::user();
        $wishlist = $user->wishlists()->first();
        if ($wishlist == null) {
            return [];
        }

        $wishlist->products()->detach($id);

        return response('Product removed to wishlist');
    }


    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'birthday' => 'required|date',
            "address" => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response($validator->errors());
        }


        $user = Auth::user();
        $this->authorize('updateCustomer', $user);

        $user->username = $request->input('username');
        $user->email = $request->input('email');
        if ($request->has('password')) {
            $user->password_hash = bcrypt($request->input('password'));
        }
        $user->date = $request->input('birthday');
        $user->address = $request->input('address');

        $file = Input::file('photo');
        if ($file != null)
            $this->storeNewPhoto($user, $file);
        $user->save();
        return response('Saved successfully');
    }
}