@php
@extends('layouts/layoutMaster')

@section('title', 'Post Details')

@section('content')
<div class="container-fluid container-p-y">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <a href="{{ route('dashboard') }}" class="btn btn-label-secondary mb-4">
                <i class="ri-arrow-left-line me-1"></i> Back to Feed
            </a>

            {{-- JUST INCLUDE THE CARD.
                 It now contains the images, content, AND the comment form/list.
                 No need to duplicate code here. --}}
            @include('posts._post_card', ['post' => $post])

        </div>
    </div>
</div>
@endsection
