<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Struktural;

use Validator;
use Auth;

class StrukturalController extends Controller
{


    public function index()
    {
      $struktural = struktural::get();

      return view('pages.struktural.index', compact('struktural'));
    }

    public function store(Request $request)
    {
      $message = [
        'nama.required' => 'Wajib di isi',
      ];

      $validator = Validator::make($request->all(), [
        'nama' => 'required',
      ], $message);

      if($validator->fails())
      {
        return redirect()->route('struktural.index')->withErrors($validator)->withInput();
      }

      $set = new struktural;
      $set->nama = $request->nama;
      $set->status = 1;
      $set->actor = Auth::user()->pegawai_id;
      $set->save();

      return redirect()->route('struktural.index')->with('berhasil', 'Berhasil Menambahkan Data Struktural');
    }

}
