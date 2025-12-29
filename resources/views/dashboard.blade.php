@extends('layout')

@section('content')
<div class="text-center mt-5">
    <h1>Olá, {{ Auth::user()->name }}!</h1>
    <p class="lead">Bem-vindo ao SGCM.</p>
    
    <div class="alert alert-info d-inline-block">
        Você tem perfil de: <strong>{{ Auth::user()->profile }}</strong>.
        <br>
        Acesse suas tarefas no menu acima.
    </div>
    
    @if(session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif
</div>
@endsection