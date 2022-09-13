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
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {

        $product = new Product;
        $product->nama_barang=$req->input('nama_barang');
        $product->stok_tersedia=$req->input('stok');
        $product->save();

        $id_product = $product->id;

         $detail = new DetailProduct;
         $detail->id_barang = $id_product;
         $detail->jenis_barang = $req->input('jenis_barang');
         $detail->seluruh_stok = $req->input('stok');
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
                  SELECT B.*,  D.jenis_barang, D.seluruh_stok,
                  T.tanggal_transaksi, stok_terjual
                  FROM barang as B
                   INNER JOIN detail_barang as D
                   ON B.id_barang = D.id_barang
                   LEFT JOIN transaksi_barang as T
                   ON B.id_barang = T.id_barang
                   WHERE tanggal_transaksi=(
                     SELECT max(tanggal_transaksi)
                     from transaksi_barang
                     WHERE id_barang = T.id_barang
                     )
                     OR tanggal_transaksi IS NULL
                     ORDER BY B.id_barang
                     "));
          if ($query) {
            $rsp['code'] = 200;
            $rsp['message'] = 'Success';
            $rsp['data'] = $query;
            return response()->json($rsp, 200);
          }
    }

    function detailProduct($id){
      $query = DB::select(
                  DB::raw("
                  SELECT B.*,D.jenis_barang, D.seluruh_stok,
                  T.id_transaksi, T.tanggal_transaksi, T.stok_terjual
                  FROM barang as B
                   INNER JOIN detail_barang as D
                   ON B.id_barang = D.id_barang
                   LEFT JOIN transaksi_barang as T
                   ON B.id_barang = T.id_barang
                   WHERE B.id_barang = $id
                   ORDER BY id_transaksi DESC"));
      if ($query) {
        $rsp['code'] = 200;
        $rsp['message'] = 'Success';
        $rsp['data'] = $query;
        return response()->json($rsp);
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
       $detail = DetailProduct::where('id_barang',$id)->first();
         $on_stock = $product->stok_tersedia;
         $input_stock = $req->stok;
         $new_stock = $detail->seluruh_stok;

         if ($input_stock != $on_stock){
           $new_stock += $input_stock - $on_stock;
         }

       $update_product = $product->where('id_barang', $id)->update([
                           'nama_barang' => $req->nama_barang,
                           'stok_tersedia' => $req->stok
                         ]);

       $update_detail = $detail->where('id_barang', $id)->update([
                         'jenis_barang' => $req->input('jenis_barang'),
                         'seluruh_stok' => $new_stock
                          ]);

        if($update_product && $update_detail){
          $rsp['code'] = 200;
          $rsp['message'] = 'Barang berhasil diupdate';
          return response()->json($rsp, 200);
        } else {
          $rsp['code'] = 403;
          $rsp['message'] = 'Barang gagal diupdate';
          return response()->json($rsp, 403);
        }
      } else {
        return response()->json(['message' => 'Barang tidak ditemukan'], 404);
      }
    }


        function deleteProduct($id){
          $query = Product::where('id_barang',$id)->delete();
          if ($query) {
          return response()->json(['Message' => 'produk berhasil dihapus'], 204);
        } else {
          return response()->json(['Message' => 'produk yang akan dihapus tidak ditemukan'], 404);
        }
        }

}
