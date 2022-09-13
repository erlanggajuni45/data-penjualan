<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiBarang extends Model
{
  protected $table = "transaksi_barang";
  protected $fillable = ["id_barang", "tanggal_transaksi", "stok_terjual"];

  public $timestamps = false;

  function Product()
  {
    return $this->hasMany('App\Models\Product');
  }
}
