<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailProduct extends Model
{
  protected $table = "detail_barang";
  protected $fillable = ["id_barang","jenis_barang"];

  public $timestamps = false;

  function Product()
  {
    return $this->hasMany('App\Models\Product');
  }
}
