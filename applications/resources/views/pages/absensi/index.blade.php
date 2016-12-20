@extends('layout.master')

@section('title')
  <title>Detail Absensi</title>
  <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('content')

<div class="row">
  <div class="col-md-10 col-md-offset-1">
    <div class="box box-primary box-solid">
      <div class="box-header with-border">
        <div class="box-title">
          <p>Pilih SKPD & Periode</p>
        </div>
      </div>
      <form action="" method="">
      {{ csrf_field() }}
      <div class="box-body">
        <div class="row">
          <div class="col-xs-6">
            <select name="skpd_id" class="form-control select2">
              <option value="">--PILIH--</option>
              @foreach ($getSkpd as $key)
                <option value="{{ $key->id }}">{{ $key->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-xs-3">
            <input type="text" class="form-control" name="start_date" id="start_date" value="" placeholder="dd/mm/yyyy" required="">
          </div>
          <div class="col-xs-3">
            <input type="text" class="form-control" name="end_date" id="end_date" value="" placeholder="dd/mm/yyyy" required="">
          </div>
        </div>
      </div>
      <div class="box-footer">
        <button class="btn btn-block bg-purple">Pilih</button>
      </div>
      </form>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary box-solid">
      <div class="box-header">
        <h3 class="box-title">Detil Absensi</h3>
      </div>
      <div class="box-body table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Terlambat</th>
              <th>Pulang Cepat</th>
              <th>Terlambat & Pulang Cepat</th>
              <th>Tanpa Keterangan/Absen</th>
              <th>Tidak Apel</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
          </tbody>
        </table>

      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="{{ asset('plugins/select2/select2.full.min.js')}}"></script>
<script>
$(".select2").select2();
$('#start_date').datepicker({
  autoclose: true,
  format: 'dd/mm/yyyy',
  changeMonth: true,
  changeYear: true,
  showButtonPanel: true,
});
$('#end_date').datepicker({
  autoclose: true,
  format: 'dd/mm/yyyy',
  changeMonth: true,
  changeYear: true,
  showButtonPanel: true,
});

</script>
@endsection
