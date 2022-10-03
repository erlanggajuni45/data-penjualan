<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = "barang";
    protected $fillable = ["nama_barang", "stok"];

    public $timestamps = false;

    function DetailProduct()
    {
      return $this->belongsTo('App\Models\DetailProduct');
    }

    function TransaksiBarang()
    {
      return $this->belongsTo('App\Models\TransaksiBarang');
    }
}
