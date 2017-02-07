@extends('app')

@section('title')
Subscribe
@stop

@section('content')

<h1>DEMOAPP</h1>
<hr>

<div class="container">
    <div class="col-md-6 col-md-offset-3 text-center">
        <p>
            Cadastre-se e ganhe nosso exclusivo muito obrigado !!!    
        </p>
        <p> 
            <strong>{{ $qtdeSubscribed }} e-mail(s) processados</strong> 
        </p>
    </div>
</div>
<div class="container">
    <hr>
</div>
{!! Form::open() !!}
<div class="container">
    <div class="col-md-6 col-md-offset-3">
        @if (Session::has('flash_message'))
            <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                {{ Session::get('flash_message') }}
            </div>
        @endif
        
        <div class="form-group">
            {!! Form::label('email', 'Cadastre-se você também:') !!}    
            {!! Form::text('email', null, ['class'=>'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::submit('Cadastrar',['class'=>'btn btn-primary form-control']) !!}
        </div>
    </div>
</div>

{!! Form::close() !!}
@stop

