@extends('layouts.empty', ['paceTop' => true])

@section('title', 'Login Page')

@section('content')
<!-- begin login -->
<div class="login login-v1">
	<!-- begin login-container -->
	<div class="login-container">
		<!-- begin login-header -->
		<div class="login-header">
			<div class="brand">
				<img src="/images/life-logo.png" alt="" width="200">
			</div>
		</div>
		<!-- end login-header -->
		<!-- begin login-body -->
		<div class="login-body" style="background-color: #b64260;">
			<!-- begin login-content -->
			<div class="login-content">
				<form action="{{url('do_login')}}" method="POST" class="margin-bottom-0">
					{{ csrf_field() }}
					
					@if ($errors->any())
					<div class="alert alert-danger alert-dismissible fade show" role="alert">

						<ul>
							@foreach ($errors->all() as $error)
							<li><span class="alert-inner--text">{{ $error }}</li>
							@endforeach
						</ul>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					@endif
					<div class="form-group m-b-20">
						<input type="text" class="form-control form-control-lg inverse-mode" placeholder="Username" name="username" />
					</div>
					<div class="form-group m-b-20">
						<input type="password" class="form-control form-control-lg inverse-mode" placeholder="Password" name="password" />
					</div>

					<div class="login-buttons">
						<button type="submit" class="btn btn-success btn-block btn-lg" style="background-color: #e56c71;border-color: #e56c71">Masuk</button>
					</div>
				</form>
			</div>
			<!-- end login-content -->
		</div>
		<!-- end login-body -->
	</div>
	<!-- end login-container -->
</div>
<!-- end login -->
@endsection