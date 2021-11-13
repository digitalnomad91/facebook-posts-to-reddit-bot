@extends('layouts.app')
@section('title', 'History - ParserBot')
    
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
    
        
            <div class="panel panel-default">
                <div class="panel-heading">History of <span style="font-weight: 800; color: green;">Submitted</span> & <span style="font-weight: 800; color: red;">Deleted</span> Links</div>

                <div class="panel-body">
                    <table class="table">
                            <tr>
                                <td>Title</td>
                                <td>Type</td>
                                <td>Content</td>
                                <td>Created At</td>
                            </tr>
                        @foreach($pending_fb_posts as $post)
                            @php
                                $content = ($post->status_type == "link") ? $post->status_link : $post->status_message;
                            @endphp
                            <tr class="@php echo $post->reddit_status == 'success' ? 'success' : 'danger'; @endphp">
                                <td><a href="{{$post->status_link}}">{{$post->link_name}}</a></td>
                                <td>{{$post->status_type}}</td>
                                <td>{{$content}}</td>
                                <td>@php echo \Carbon\Carbon::createFromTimeStamp(strtotime($post->created_at))->diffForHumans(); @endphp</td>
                                    
                            </tr>
                        @endforeach
                    </table>
                        
                    {{ $pending_fb_posts->links() }}

                </div>
            </div>

                
        </div>
    </div>
</div>
@endsection
