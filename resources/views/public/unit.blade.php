@extends('layouts.public')
@section('title', $businessUnit->name)
@section('content')
<x-unit-detail :businessUnit="$businessUnit" />
@endsection