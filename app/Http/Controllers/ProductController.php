<?php

namespace App\Http\Controllers;

use App\Models\Product;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(10);
        return view('index', compact('products'));
    }

    public function create()
    {
        return view('create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:255'],
            'image' => ['required', 'image', 'max:2048'],
            'price' => ['required', 'numeric'],
            'description' => 'required',
        ]);

        try {
            $cloudinaryImage = $request->file('image')->storeOnCloudinary('products');
            $url = $cloudinaryImage->getSecurePath();
            $public_id = $cloudinaryImage->getPublicId();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['image' => 'Failed to upload the image. Please try again.']);
        }

        Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image_url' => $url,
            'image_public_id' => $public_id,
        ]);

        return redirect()->route('products.index')->with('message', 'Created successfully');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Product $product)
    {
        return view('edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => ['sometimes', 'required', 'max:255'],
            'image' => ['sometimes', 'required', 'image', 'max:2048'],
            'price' => ['sometimes', 'required', 'numeric'],
            'description' => ['sometimes', 'required'],
        ]);

        if ($request->hasFile('image')) {
            Cloudinary::destroy($product->image_public_id);
            $cloudinaryImage = $request->file('image')->storeOnCloudinary('products');
            $url = $cloudinaryImage->getSecurePath();
            $public_id = $cloudinaryImage->getPublicId();

            $product->update([
                'image_url' => $url,
                'image_public_id' => $public_id,
            ]);
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price
        ]);

        return redirect()->route('products.index')->with('message', 'Updated successfully');
    }

    public function destroy(Product $product)
    {
        Cloudinary::destroy($product->image_public_id);
        $product->delete();

        return redirect()->route('products.index')->with('message', 'Deleted Successfully');
    }
}
