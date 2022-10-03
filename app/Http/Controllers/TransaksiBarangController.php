<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Validator;
use App\Models\TransaksiBarang;
use App\Models\Product;
use App\Models\DetailProduct;

class TransaksiBarangController extends Controller
{
    function listTransaction() {
      $query = DB::select(
                  DB::raw("
                      SELECT B.id_barang, B.nama_barang,
                      D.harga, D.jenis_barang,
                      T.id_transaksi, T.tanggal_transaksi, T.stok_terjual
                      FROM barang as B
                      INNER JOIN detail_barang as D
                      ON B.id_barang = D.id_barang
                      INNER JOIN transaksi_barang as T
                      ON B.id_barang = T.id_barang
                      ORDER BY T.tanggal_transaksi
                     "));
          if ($query) {
            $rsp['code'] = 200;
            $rsp['message'] = 'Success';
            $rsp['data'] = $query;
            return response()->json($rsp, 200);
          }
    }

    function addTransaction(Request $req)
    {
        $product = Product::where('id_barang', $req->id_barang)->first();
        if($product){
          if($product->stok >= $req->stok_terjual){
              $validator = Validator::make($req->all(), [
                  'tanggal_transaksi' => 'required|date',
                  'stok_terjual' => 'required|integer',
              ]);
              if ($validator->fails()) {
                  return response()->json($validator->messages(), 400);
              } else {
                $trx = new TransaksiBarang;
                $trx->tanggal_transaksi = $req->tanggal_transaksi;
                $trx->stok_terjual = $req->stok_terjual;
                $trx->id_barang = $req->id_barang;
                $trx->save();
                    $update_stock = $product->where('id_barang', $req->id_barang)
                                    ->update([
                                      'stok' => $product->stok - $req->stok_terjual,
                                    ]);
                    $rsp['code'] = 201;
                    $rsp['message'] = 'Transaksi berhasil ditambahkan';
                    $rsp['data'] = $trx;
                    return response()->json($rsp, 201);
             }
           } else {
              $rsp['code'] = 403;
              $rsp['message'] = 'Stok terjual maksimal ' .$product->stok;
              return response()->json($rsp, 403);
            }
          } else {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
          }
    }

    function deleteTransaction($id){
      $get_trx = TransaksiBarang::where('id_transaksi',$id);

      if($get_trx){
      $stock_trx = $get_trx->first()->stok_terjual;
      $id_product_trx = $get_trx->first()->id_barang;
      $new_stock = Product::where('id_barang', $id_product_trx)
                   ->first()->stok + $stock_trx;

      $update_product = Product::where('id_barang', $id_product_trx)
                        ->update([
                          'stok' => $new_stock,
                        ]);
          if($update_product) {
            $delete = $get_trx->delete();
            if($delete) {
              return response()->json('', 204);
            } else {
              return response()->json(['message' => 'Barang gagal dihapus'], 422);
            }
          }
        } else {
          return response()->json(['message' => 'Barang yang akan dihapus tidak ditemukan'], 404);
        }

    }

      function editTransaction($id)
        {
          $trx_get = TransaksiBarang::where('id_transaksi', $id)->get();
          if($trx_get) {
            $rsp['code'] = 200;
            $rsp['message'] = "Success";
            $rsp['data'] = $trx_get;
            return response()->json($rsp, 200);
          } else {
            return response()->json(['message' => 'Data transaksi tidak ditemukan'], 404);
          }
        }

      function updateTransaction($id, Request $req)
        {
           $detail = TransaksiBarang::where('id_transaksi',$id);
           if($detail->first()) {
             $validator = Validator::make($req->all(), [
                 'tanggal_transaksi' => 'required|date',
                 'stok_terjual' => 'required|integer',
             ]);
             if ($validator->fails()) {
                 return response()->json($validator->messages(), 400);
             } else {
             $old_sold = $detail->first()->stok_terjual;
             $id_product = $detail->first()->id_barang;
             $stock_ready = Product::where('id_barang', $id_product)
                                    ->first()->stok;

             if($old_sold != $req->stok_terjual){
               if($stock_ready + $old_sold >= $req->stok_terjual){
               $diff_stock = $old_sold - $req->stok_terjual;
               $update_stock = $stock_ready + $diff_stock;
                         Product::where('id_barang', $detail->first()->id_barang)
                                  ->update([
                                      'stok' => $update_stock
                                  ]);
               } else {
                 return response()->json(['message' => 'Stok keseluruhan tidak boleh lebih dari ' .$stock_ready + $old_sold], 403);
               }
             }
           $update_trx = $detail->update([
                         'tanggal_transaksi' => $req->tanggal_transaksi,
                         'stok_terjual' => $req->stok_terjual,
                          ]);
           if($update_trx) {
             $rsp['code'] = 200;
             $rsp['message'] = 'Update transaksi berhasil';
             $rsp['data'] = $update_trx;
             return response()->json($rsp, 200);
           } else {
             return response()->json(['message' => 'Update transaksi gagal'], 403);
           }
           }
        } else {
          return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }
      }
}
