@extends('layouts.default')

@section('title', 'Blank Page')

@section('content')
<!-- begin breadcrumb -->

<!-- end breadcrumb -->
<!-- begin page-header -->
<h1 class="page-header">{{ $title }} <small>{{ $sub_title}}</small></h1>
<!-- end page-header -->



<!-- begin panel -->


<div class="panel panel-inverse">

	<div class="panel-body">
		@include('includes.component.erorr-message')
		@include('includes.component.success-message')
		<form action="{{ route('import-statement') }}" method="POST" enctype="multipart/form-data" id="import-form" class="mb-3">
			{{ csrf_field() }}
			<div class="form-group">
				<label for="note-input">Notes</label>
				<textarea class="form-control" id="note-input" rows="3" name="note"></textarea>
			</div>
			<div class="form-group">
				<label for="tanggal-input" class="col-form-label">Tanggal :</label>
				<input type="text" class="form-control" id="tanggal-input" name="tanggal">
			</div>
			<div class="form-group">
				<label for="fileExcel" class="col-form-label">FIle :</label>
				<div class="custom-file">

					<input type="file" class="custom-file-input" name="file" id="fileExcel" required>
					<label class="custom-file-label" for="fileExcel">Choose file...</label>
					<div class="invalid-feedback">Example invalid file </div>
				</div>
			</div>
			<div class="align-item-center text-center">
				<button type="submit" form="import-form" class="btn btn-pink "><i class="fa fa-upload">&nbsp;</i>Import Excel</button>
			</div>
		</form>

		<div class="table-responsive">
			<table class="table table-striped m-b-0">
				<thead class="bg-pink text-white">
					<tr>
						<th class="text-center">No.</th>
						<th>Tanggal</th>
						<th>Note</th>
						<th>File Import</th>
						<th>File Report</th>
					</tr>
				</thead>
				<tbody>
					@forelse ($datas as $value)
					<tr>
						<td class="text-center">
							{{ $loop->iteration }}
						</td>
						<td>{{ \Carbon\Carbon::parse($value->import_date)->isoFormat('dddd, D MMMM Y')}}</td>
						<td>{{ $value->note}}</td>
						<td> <a href="/{{ ($value->file_import) }}" class="btn btn-pink btn-sm  m-r-2"><i class="fa fa-download"></i> Import</a></td>
						<td> <a href="/{{ ($value->file_report) }}" class="btn btn-pink btn-sm  m-r-2"><i class="fa fa-download"></i> Download</a></td>

					</tr>
					@empty
					<tr>
						<td class="text-center text-mute" colspan="4">Data tidak tersedia</td>
					</tr>
					@endforelse
				</tbody>
			</table>
			<nav aria-label="Page navigation example " class="mt-3">
				{{ $datas->links() }}
			</nav>
		</div>
	</div>
</div>
<!-- end panel -->

@endsection