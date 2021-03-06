<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    public $timestamps  = false;
    protected $table = 'product';

    public function orders()
    {
        return $this->belongsToMany('App\Order', 'product_order', 'id_product', 'id_order')->withPivot('quantity');
    }

    public function wishlists()
    {
        return $this->belongsToMany('App\Wishlist', 'wishlist_product', 'id_product', 'id_wishlist');
    }

    public function shoppingCart()
    {
        return $this->belongsToMany('App\User', 'shopping_cart')->withPivot('quantity');
    }

    public function discounts()
    {
        return $this->belongsToMany('App\Discount', 'apply_discount', 'id_product', 'id_discount');
    }

    public function images()
    {
        return $this->belongsToMany('App\Image', 'product_image', 'id_product', 'id_image');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Tag', 'product_tag', 'id_product', 'id_tag');
    }

    public static function getTopSales()
    {
        $top_items = DB::table('product')
                        ->select('product.id','name','price')
                        ->join('apply_discount','product.id','=','apply_discount.id_product')
                        ->join( 'discount' ,'discount.id','=','apply_discount.id_discount')
                        ->where([['date_begin', '<=', date('Y-m-d')], ['date_end', '>=',  date('Y-m-d')]])
                        ->orderByDesc('views')
                        ->limit(4);
        $product_imgs = DB::table('image')
                            ->select('top_items.id as id','top_items.name','price','img_name as img','description as alt')
                            ->join('product_image','product_image.id_image','=','image.id')
                            ->joinSub($top_items, 'top_items',function($join) {
                                $join->on('top_items.id','=','product_image.id_product');
                            })
                            ->get();

        //parse the images directories
        foreach($product_imgs as $product) {
            $product->sale_price = Product::getSalePrice($product->id);
            $product->alt = nl2br(str_replace(" ", "&nbsp;", $product->alt));
        }

        return $product_imgs;
    }
    
    public static function getTopByTag($tag)
    {
        $top_items = DB::table('product')
            ->select('product.id', 'product.name', 'price')
            ->join('product_tag', 'product.id', '=', 'product_tag.id_product')
            ->join('tag', 'tag.id', '=', 'product_tag.id_tag')
            ->where('tag.name', $tag)
            ->orderByDesc('views')
            ->limit(4);
        $product_imgs = DB::table('image')
            ->select('top_items.id as id', 'top_items.name', 'price', 'img_name as img', 'description as alt')
            ->join('product_image', 'product_image.id_image', '=', 'image.id')
            ->joinSub($top_items, 'top_items', function ($join) {
                $join->on('top_items.id', '=', 'product_image.id_product');
            })
            ->get();

        //parse the images directories
        foreach ($product_imgs as $product) {
            $product->sale_price = Product::getSalePrice($product->id);
            $product->alt = nl2br(str_replace(" ", "&nbsp;", $product->alt));
        }

        return $product_imgs;
    }

    public static function getShoppingCart($user_id)
    {
        $products = DB::table('shopping_cart')
            ->select('product.name', 'product.price', 'quantity', 'product.id as id')
            ->join('product', 'product.id', '=', 'id_product')
            ->where('id_user', '=', $user_id);

        $product_imgs = DB::table('image')
            ->select('products.id as id', 'products.name', 'quantity', 'products.price', 'img_name as img', 'image.description as alt')
            ->join('product_image', 'product_image.id_image', '=', 'image.id')
            ->joinSub($products, 'products', function ($join) {
                $join->on('products.id', '=', 'product_image.id_product');
            })
            ->get();

        foreach ($product_imgs as $product) {
            $sale_price = Product::getSalePrice($product->id);
            $product->price = $sale_price == -1 ? $product->price : $sale_price;
            $product->alt =  nl2br(str_replace(" ", "&nbsp;", $product->alt));
        }

        return $product_imgs;
    }

    public static function deleteShoppingCartProduct($user_id, $product_id)
    {
        $products = DB::table('shopping_cart')
            ->where('id_product', '=', $product_id)
            ->where('id_user', '=', $user_id)
            ->delete();
    }

    public static function getShoppingCartIds($user_id)
    {
        $products = DB::table('shopping_cart')
            ->select('quantity as qty', 'id_product as id')
            ->where('id_user', '=', $user_id)
            ->get();

        return $products;
    }

    public static function deleteShoppingCartIds($user_id)
    {
        $products = DB::table('shopping_cart')
            ->select('quantity as qty', 'id_product as id')
            ->where('id_user', '=', $user_id)
            ->delete();

        return $products;
    }

    public static function getOrderProducts($id_order)
    {
        $products = DB::table('product_order')
            ->select('product.name', 'product.price', 'quantity', 'product.id as id_product')
            ->join('product', 'product.id', '=', 'product_order.id_product')
            ->where('product_order.id_order', '=', $id_order);

        $product_imgs = DB::table('image')
            ->select('products.id_product', 'products.name', 'quantity', 'products.price', 'img_name as img', 'image.description as alt')
            ->join('product_image', 'product_image.id_image', '=', 'image.id')
            ->joinSub($products, 'products', function ($join) {
                $join->on('products.id_product', '=', 'product_image.id_product');
            })
            ->get();
        $date = Order::find($id_order)->order_date;
        foreach ($product_imgs as $product) {
            $sale_price = Product::getSalePrice($product->id_product, $date);
            $product->price = $sale_price == -1 ? $product->price : $sale_price;
            $product->alt = nl2br(str_replace(" ", "&nbsp;", $product->alt));
        }

        return $product_imgs;
    }

    public static function getQuantityByID($product, $user)
    {
        $product = DB::table('shopping_cart')
            ->select('quantity')
            ->join('product', 'product.id', '=', 'shopping_cart.id_product')
            ->where('shopping_cart.id_user', '=', $user)
            ->where('shopping_cart.id_product', '=', $product)
            ->get()
            ->first();

        return $product;
    }

    public static function updateQuantity($product, $user, $quantity)
    {
        $product = DB::table('shopping_cart')
            ->join('product', 'product.id', '=', 'shopping_cart.id_product')
            ->where('shopping_cart.id_product', '=', $product)
            ->where('shopping_cart.id_user', '=', $user)
            ->update(['quantity' => $quantity]);
    }

    public static function updateStock($id, $quantity)
    {
        $product = Product::find($id);
        $product->stock = $product->stock - $quantity;
        $product->save();
    }


    public static function getByID($id)
    {
        $product_img = DB::table('product')
            ->select('product.id', 'product.name', 'price', 'product.stock', 'product.description', 'img_name as img', 'image.description as alt')
            ->join('product_image', 'product_image.id_product', '=', 'product.id')
            ->join('image', 'image.id', '=', 'product_image.id_image')
            ->where('product.id', '=', $id)
            ->first();
        if (!$product_img)
            return null;
        $product_img->sale_price = Product::getSalePrice($id);
        $product_img->alt = nl2br(str_replace(" ", "&nbsp;", $product_img->alt));
        return $product_img;
    }

    public static function getStockProducts($page)
    {
        $products = DB::table('product')
            ->select('name', 'price', 'stock', 'id')
            ->orderBy('id')
            ->limit(10)
            ->offset($page * 10)
            ->get();

        return $products;
    }

    public static function getRelatedProducts($product)
    {
        return DB::table('product')
            ->select(DB::raw('count(*) as related_count'), 'product.id', 'product.name', 'product.price', 'image.description', 'product.name', 'img_name as img')
            ->join('product_image', 'product_image.id_product', '=', 'product.id')
            ->join('image', 'image.id', '=', 'product_image.id_image')
            ->join('product_tag', 'product_tag.id_product', '=', 'product.id')
            ->join('tag', 'product_tag.id_tag', '=', 'tag.id')
            ->where('product.id', '!=', $product)
            ->whereRaw("(select count(*) from tag, product_tag where tag.id = product_tag.id_tag and product_tag.id_product = $product) > 0")
            ->groupBy('product.id', 'image.id')
            ->orderByDesc('related_count')
            ->limit(3)
            ->get()
            ->all();

        // for()
    }

    public static function getSalePrice($id, $date = null)
    {
        $product = Product::find($id);
        if ($date) {
            $discount = $product->discounts()->where([['date_begin', '<=', $date], ['date_end', '>=', $date]])->first();
        } else {
            $discount = $product->discounts()->where([['date_begin', '<=', now()], ['date_end', '>=', now()]])->first();
        }
        return $discount ? round($product->price * ((100 - $discount->percentage) / 100), 2) : -1;
    }
}
