<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TaLog;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Skpd;

use Auth;
use DB;
use Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pegawai_id = Auth::user()->pegawai_id;

        if(session('status') == 'administrator')
        {
          $jumlahPegawai = pegawai::count();
        }
        elseif(session('status') == 'admin')
        {
          $jumlahPegawai = pegawai::where('skpd_id', Auth::user()->skpd_id)->count();
        }

        $tpp = pegawai::where('id', $pegawai_id)->select('tpp_dibayarkan', 'fid')->first();

        $filterSKPD = pegawai::join('preson_skpd', 'preson_skpd.id', '=', 'preson_pegawais.skpd_id')
                            ->select('preson_skpd.*', 'preson_pegawais.nama as nama_pegawai', 'preson_pegawais.fid', 'preson_pegawais.tpp_dibayarkan')
                            ->get();

        $month = "08";
        $year = "2016";

        $start_date = "01-".$month."-".$year;
        $start_time = strtotime($start_date);

        $end_time = strtotime("+1 month", $start_time);
        $pegawai = pegawai::select('preson_skpd.nama as nama_skpd')->join('preson_skpd', 'preson_pegawais.skpd_id', '=', 'preson_skpd.id')->get();


        if(session('status') == 'administrator')
        {
          $tanggalini = date('d/m/Y');
          $tanggalinter = date('Y-m-d');

          $jumlahintervensi = DB::select("select c.nama, count(*) as 'jumlah_intervensi'
          from preson_intervensis a
          join preson_pegawais b on a.pegawai_id = b.id
          join preson_skpd c on b.skpd_id = c.id
          where a.tanggal_mulai <= '$tanggalinter'
          and a.tanggal_akhir >= '$tanggalinter'
          group by c.nama");

          $absensi = DB::select("select id, skpd, count(*) as 'jumlah_hadir'
                                  from
                                  (select c.id, c.nama as skpd, count(*) as kk
                                  from ta_log a join preson_pegawais b
                                  on a.fid = b.fid
                                  join preson_skpd c on b.skpd_id = c.id
                                  where tanggal_log='$tanggalini'
                                  group by c.nama, a.fid) as ab
                                  group by skpd");

          return view('home', compact('absensi', 'pegawai', 'jumlahintervensi', 'tpp', 'jumlahPegawai'));
        }
        else if(session('status') == 'admin')
        {
          $tanggalini = date('d/m/Y');
          $absensi = DB::select("SELECT pegawai.fid, pegawai.nama as nama_pegawai, Tanggal_Log,
                                				Jam_Datang, Jam_Pulang
                                	FROM (select nama, fid from preson_pegawais where preson_pegawais.skpd_id = 15) as pegawai

                                	LEFT OUTER JOIN (select MIN(Jam_Log) as Jam_Datang, ta_log.Fid as Fid from ta_log, preson_pegawais
                                										where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                										and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '08:00:00'
                                										and ta_log.Fid = preson_pegawais.fid
                                										and preson_pegawais.skpd_id = 15) as tabel_Jam_Datang
                                	ON pegawai.fid = tabel_Jam_Datang.Fid

                                	LEFT OUTER JOIN (select MIN(Jam_Log) as Jam_Pulang, ta_log.Fid as Fid from ta_log, preson_pegawais
                                										where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                										and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '16:00:00'
                                										and ta_log.Fid = preson_pegawais.fid
                                										and preson_pegawais.skpd_id = 15) as tabel_Jam_Pulang
                                	ON pegawai.fid = tabel_Jam_Pulang.Fid

                                	LEFT OUTER JOIN (select Tanggal_Log, ta_log.Fid as Fid from ta_log, preson_pegawais
                                										where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                										and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '16:00:00'
                                										and ta_log.Fid = preson_pegawais.fid
                                										and preson_pegawais.skpd_id = 15 LIMIT 1) as tabel_Tanggal_Log
                                	ON pegawai.fid = tabel_Tanggal_Log.Fid");
          $absensi = collect($absensi);
          return view('home', compact('absensi', 'pegawai', 'list', 'tpp', 'jumlahPegawai'));
        }else{
          for($i=$start_time; $i<$end_time; $i+=86400)
          {
            $tanggalini = date('d/m/Y', $i);
            $list[] = DB::select("SELECT c.nama AS skpd, b.nama AS nama_pegawai, a.Tanggal_Log, a.DateTime,
                                    (select MIN(Jam_Log) from ta_log
                                  		where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  		and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') < '08:00:00'
                                  		and Fid = '$tpp->fid') as Jam_Datang,
                                  	(select MIN(Jam_Log) from ta_log
                                  		where DATE_FORMAT(STR_TO_DATE(Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  		and TIME_FORMAT(STR_TO_DATE(Jam_Log,'%H:%i:%s'), '%H:%i:%s') > '16:00:00'
                                  		and Fid = '$tpp->fid') as Jam_Pulang
                                  FROM ta_log a, preson_pegawais b, preson_skpd c
                                  WHERE b.skpd_id = c.id
                                  AND a.Fid = b.fid
                                  AND a.Fid = '$tpp->fid'
                                  AND DATE_FORMAT(STR_TO_DATE(a.Tanggal_Log,'%d/%m/%Y'), '%d/%m/%Y') = '$tanggalini'
                                  LIMIT 1");
          }
          $absensi = collect($list);
          return view('home', compact('absensi', 'pegawai', 'list', 'tpp', 'jumlahPegawai'));
        }
    }

    public function detailabsensi($id)
    {
      $getskpd = skpd::find($id);

      $tanggalini = date('d/m/Y');
      $pegawai = pegawai::where('skpd_id', $id)->get();
      $absensi = DB::select("select a.fid, nama, tanggal_log, jam_log
                              from (select fid, nama from preson_pegawais where skpd_id = 15) as a
                              left join ta_log b on a.fid = b.fid
                              where b.tanggal_log = '$tanggalini'");

      return view('pages.absensi.detailabsen')
        ->with('absensi', $absensi)
        ->with('pegawai', $pegawai)
        ->with('getskpd', $getskpd);
    }

}
