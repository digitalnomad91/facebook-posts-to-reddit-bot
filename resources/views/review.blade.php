@extends('layouts.app')

@section('title', 'Review Needed - ParserBot')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
    
        
            <div class="panel panel-default">
                <div class="panel-heading">Review Needed before Submission</div>

                <div class="panel-body">
                    <table class="table">
                            <tr>
                                <td>Name</td>
                                <td></td>
                                <td>Link</td>
                                <td>Created At</td>
                                <td><div class="pull-right"><input type="checkbox"></div></td>
                            </tr>
                        @foreach($pending_fb_posts as $post)
                            <tr>
                                <td>
                                    <input type="text" class="form-control" value="@php echo $post->link_name ? $post->link_name : ($post->status_message ? $post->status_message : "No title") @endphp" style="min-width: 350px;">
                                    
                                </td>
                                <td>
                                    <button type="button" class="btn btn-default"><i class="fa fa-plus"></i></button>

                                </td>
                                <td>
                                   <a href="{{$post->status_link}}">@php echo (strlen($post->status_link) > 50) ? substr($post->status_link, 0, 50)."..." : $post->status_link @endphp</a>
                                </td>
                                <td>@php echo \Carbon\Carbon::createFromTimeStamp(strtotime($post->created_at))->diffForHumans(); @endphp</td>
                                <td>
                                    <div class="pull-right">
                                    <a href="javascript:;" class="btn btn-danger"><input type="checkbox"></a>
                                    </div>
                                </td>
                                    
                            </tr>
                        @endforeach
                    </table>
                        
                    {{ $pending_fb_posts->links() }}
                    <div class="pull-right">
                    <button type="button" class="btn btn-danger"><i class="fa fa-trash"></i> Delete Links</button>
                    </div> 

                </div>
            </div>
            </div>

                
        </div>
    </div>
</div>
@endsection
