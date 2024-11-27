<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    public function index()
    {
        $admin_id = Session::get('admin_id');

        if (!$admin_id) {
            return redirect('login');
        }

        $products = DB::table('products')->get();

        return view('admin_products', ['products' => $products]);
    }

    public function store(Request $request)
    {
        $admin_id = Session::get('admin_id');

        if (!$admin_id) {
            return redirect('login');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $name = $request->input('name');
        $price = $request->input('price');
        $image = $request->file('image')->store('uploaded_img', 'public');

        DB::table('products')->insert([
            'name' => $name,
            'price' => $price,
            'image' => $image,
        ]);

        return redirect('admin_products')->with('success', 'Product added successfully!');
    }

    public function destroy($id)
    {
        $admin_id = Session::get('admin_id');

        if (!$admin_id) {
            return redirect('login');
        }

        $product = DB::table('products')->where('id', $id)->first();
        if ($product) {
            Storage::disk('public')->delete($product->image);
            DB::table('products')->where('id', $id)->delete();
        }

        return redirect('admin_products')->with('success', 'Product deleted successfully!');
    }

    public function edit($id)
    {
        $admin_id = Session::get('admin_id');

        if (!$admin_id) {
            return redirect('login');
        }

        $product = DB::table('products')->where('id', $id)->first();

        if (!$product) {
            return redirect('admin_products');
        }

        return view('admin_products_edit', ['product' => $product]);
    }

    public function update(Request $request, $id)
    {
        $admin_id = Session::get('admin_id');

        if (!$admin_id) {
            return redirect('login');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $name = $request->input('name');
        $price = $request->input('price');
        $image = $request->file('image');

        $product = DB::table('products')->where('id', $id)->first();

        if ($image) {
            Storage::disk('public')->delete($product->image);
            $imagePath = $image->store('uploaded_img', 'public');
            DB::table('products')->where('id', $id)->update([
                'name' => $name,
                'price' => $price,
                'image' => $imagePath,
            ]);
        } else {
            DB::table('products')->where('id', $id)->update([
                'name' => $name,
                'price' => $price,
            ]);
        }

        return redirect('admin_products')->with('success', 'Product updated successfully!');
    }
}
