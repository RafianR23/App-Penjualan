<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kategori;
use Validator;
use Storage; //menggunakan file multimedia

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $kategori = Kategori::paginate(5);
        $filterKeyword = $request->get('keyword');
        if($filterKeyword)
        {
            //dijalankan jika ada pencarian
            $kategori = Kategori::where('kategori','LIKE',"%$filterKeyword%")->paginate(5);
        }
        return view('kategori.index',compact('kategori'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('kategori.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input,[
            'kategori'=>'required|max:255',
            'gambar_kategori'=>'required|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        if($validator->fails())
        {
            return redirect()->route('kategori.create')->withErrors($validator)->withInput(); 
        }

        //Setting Upload File
        $gambar_kategori = $request->file('gambar_kategori'); //Tangkap File
        $extention = $gambar_kategori->getClientOriginalExtension(); //digunakan untuk menangkap file ekstension dari gambar

        //cek validasi jika valid akan menjalankan perintah didalamnya
        if($request->file('gambar_kategori')->isValid()){
            $namaFoto = "kategori/".date('YmdHis').".".$extention; //membuat nama foto dan membuat tanggal upload
            $upload_path = 'public/uploads/kategori'; //posisi meletakan folder laravel akan membuat sendiri
            $request->file('gambar_kategori')->move($upload_path,$namaFoto); 
            $input['gambar_kategori'] = $namaFoto;
        }

        //memasukan data ke tabel kategori
        Kategori::create($input); 
        return redirect()->route('kategori.index')->with('status','Kategori Berhasil disimpan');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('kategori.edit',compact('kategori'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $input = $request->all();

        $validator = Validator::make($input,[
            'kategori'=>'required|max:255',
            'gambar_kategori'=>'sometimes|nullable|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        if($validator->fails())
        {
            return redirect()->route('kategori.edit',[$id])->withErrors($validator);
        }

        if($request->hasFile('gambar_kategori')){
            if($request->file('gambar_kategori')->isValid())
            {
                Storage::disk('upload')->delete($kategori->gambar_kategori);

                $gambar_kategori = $request->file('gambar_kategori');
                $extention = $gambar_kategori->getClientOriginalExtension();
                $namaFoto = "kategori/".date('YmdHis').".".$extention;
                $upload_path = 'public/uploads/kategori';
                $request->file('gambar_kategori')->move($upload_path,$namaFoto);
                $input['gambar_kategori'] = $namaFoto;

            }   
        }

        $kategori->update($input);
        return redirect()->route('kategori.index')->with('status','Kategori Berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();
        Storage::disk('upload')->delete($kategori->gambar_kategori); //delete berdasarkan nama file gambarnya
        return redirect()->route('kategori.index')->with('status','Kategori berhasil dihapus');
    }
}
