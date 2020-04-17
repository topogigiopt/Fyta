@extends('layouts.app', ['scripts' => ['js/register.js'], 'styles' => ['css/registerpage.css']])

@section('content')
<div class="container">
  <form action="/register" method="POST" class=".form form justify-content-center" id='registerForm'>
    <div class="row ">
      <div class="col">
        <h1 class="text-center form-title">Register</h1>
      </div>
    </div>
    <!--  -->
    <div class="row ">
      <img src={{asset("img/user.png")}} class="mx-auto d-block img-fluid rounded-circle border border-dark rounded" alt="User Image" id="user-img">

    </div>
    <!-- -->
    <div class="row form-group ">
      <div class="col">
        <input type="text" name="username" id="username" class="form-control registerinput" placeholder="Username" aria-describedby="helpUser">
        <input type="email" name="email" id="email" class="form-control registerinput" placeholder="Email" aria-describedby="helpId">
        <input type="text" name="address" id="address" class="form-control registerinput" placeholder="Address" aria-describedby="helpId">
      </div>
    </div>
    <!--  -->
    <div class="row">
      <div class="col">
        <h4 id="birthday">Birthday</h4>
      </div>
    </div>
    <!--  -->
    <input name="birthday" type="hidden" value="" id="birthday"/>
    <div class="row form-group  birthday">
      <div class="col ">
        <select class="custom-select custom-select-sm registerinput registerSelect" name="day" id="day">
          <option selected class="text-muted optionplaceholder" hidden>Day</option>
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
        </select>
      </div>
      <div class="col ">
        <select class="custom-select custom-select-sm registerinput registerSelect" name="month" id="month">
          <option selected class="text-muted optionplaceholder" hidden>Month</option>
          <option value="1">January</option>
          <option value="2">February</option>
          <option value="12">December</option>
        </select>
      </div>
      <div class="col ">
        <select class="custom-select custom-select-sm registerinput registerSelect" name="year" id="year">
          <option selected class="text-muted optionplaceholder" hidden>Year</option>
          <option value="1999">1999</option>
          <option value="2000">2000</option>
          <option value="2001">2001</option>
        </select>
      </div>

    </div>
    <!--  -->
    <div class="form-group row ">
      <div class="col">
        <input type="password" name="password" id="password" class="form-control registerinput" placeholder="Password" aria-describedby="helpId">
      </div>
    </div>
    <!--  -->
    <div class="row ">
      <div class="col ">
        <input type="submit" class="btn rounded-0 btn-lg shadow-none" id="submitbutton" value="Register">
      </div>
    </div>
    {{ csrf_field() }}
  </form>
</div>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@endsection
