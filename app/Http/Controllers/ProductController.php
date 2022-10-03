<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\DetailProduct;
use App\Models\TransaksiBarang;

class ProductController extends Controller
{
    function addProduct(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'nama_barang' => 'required',
            'stok' => 'required|integer',
            'jenis_barang' => 'required',
            'harga' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
        $product = new Product;
        $product->nama_barang=$req->input('nama_barang');
        $product->stok=$req->input('stok');
        $product->save();

        $id_product = $product->id;

         $detail = new DetailProduct;
         $detail->id_barang = $id_product;
         $detail->jenis_barang = $req->jenis_barang;
         $detail->harga = $req->harga;
         $detail->save();

         if($product && $detail) {
         $rsp['code'] = 201;
         $rsp['message'] = "Tambah produk berhasil";
         $rsp['data'] = $product;
         $rsp['data']['jenis_barang'] = $detail->jenis_barang;
         return response()->json($rsp, 201);
       } else {
         return response()->json('', 422);
       }
       }
    }

    function listProduct()
    {
      $query = DB::select(
                  Db::raw("
                        SELECT B.*, D.jenis_barang, D.harga
                        FROM barang as B INNER JOIN detail_barang as D
                        ON B.id_barang = D.id_barang
                     "));
          if ($query) {
            $rsp['code'] = 200;
            $rsp['message'] = 'Success';
            $rsp['data'] = $query;
            return response()->json($rsp, 200);
          }
    }

    function editProduct($id){
        $query = DB::select(
                    Db::raw("
                    SELECT *
                    FROM barang as B
                    JOIN detail_barang as D
                    ON B.id_barang = D.id_barang
                    WHERE B.id_barang = $id"));
          if ($query) {
            $rsp['code'] = 200;
            $rsp['message'] = "Berhasil mendapatkan data produk";
            $rsp['data'] = $query;
            return response()->json($rsp, 200);
          } else {
            $rsp['code'] = 404;
            $rsp['message'] = "Gagal mendapatkan data";
            return response()->json($rsp, 404);
          }
    }

    function updateProduct($id, Request $req){
       $product = Product::where('id_barang', $id)->first();
       if($product) {

         $validator = Validator::make($req->all(), [
             'nama_barang' => 'required',
             'stok' => 'required|integer',
             'jenis_barang' => 'required',
             'harga' => 'required|integer',
         ]);
         if ($validator->fails()) {
             return response()->json($validator->messages(), 400);
         } else {
             $update_product = $product->where('id_barang', $id)->update([
                               'nama_barang' => $req->nama_barang,
                               'stok' => $req->stok
                             ]);

             $update_detail = DetailProduct::where('id_barang', $id)->update([
                             'jenis_barang' => $req->jenis_barang,
                             'harga' => $req->harga,
                              ]);

        if($update_product && $update_detail){
          $rsp['code'] = 200;
          $rsp['message'] = 'Barang berhasil diupdate';
          return response()->json($rsp, 200);
        }
        // else {
        //    return response()->json(['message' => 'Barang gagal diupdate'], 400);
        //  }
        }
      } else {
        return response()->json(['message' => 'Barang tidak ditemukan'], 404);
      }
    }


        function deleteProduct($id){
          $query = Product::where('id_barang',$id)->delete();
          if ($query) {
          return response()->json(['message' => 'produk berhasil dihapus'], 204);
        } else {
          return response()->json(['message' => 'produk yang akan dihapus tidak ditemukan'], 404);
        }
        }

}
