@extends('layouts.app')
@section('title', 'Edit Testimonial')
@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN VALIDATION STATES-->
        <div class="portlet light portlet-fit portlet-form bordered cbi-content-wrap">
			<div class="portlet-title">
                <div class="caption">
                   <span class="caption-subject font-red sbold uppercase"><i class="fa fa-edit"></i> Testimonial</span>
					<h4 class="bas-title">Edit Testimonial</h4>
                    <a href="/testimonial/" class="cbi-back-btn btn"><div class="btn-span"><img src="{{ URL::asset('assets/images/back-btn-icon.png') }}" alt="without-hover" class="without-hover">
					<img src="{{ URL::asset('assets/images/back-btn-icon-hover.png') }}" alt="with-hover" class="with-hover"></div>Back</a>
				</div>
            </div>

             <div class="portlet-body edit-form">

                     {{ Form::model($data,array('route' => array('testimonial.update', $data->id), 'method' => 'PUT','class'=>'form-vertical')) }}

                    

					<div class="form-group">
            <div class="col-md-6">
              <label class="label-title">Title<span class="required"> * </span> </label>
              {{ Form::text('title', $data->title, array('class' => 'form-control')) }}
              
                          @if ($errors->has('title'))
                              <div class="error">{{ $errors->first('title') }}</div>
                          @endif
            </div>
            <div class="col-md-6">
              <label class="label-title">Message<span class="required"> * </span> </label>
              {{ Form::textarea('message', $data->message, array('class' => 'form-control' ,'id'=>'elm1','autocomplete'=>'off')) }}
              
                          @if ($errors->has('message'))
                              <div class="error">{{ $errors->first('message') }}</div>
                          @endif
            </div>
           </div>

           
            
					<div class="form-actions cbi-save-btn-wrap">
                        <div class="row">
                            <div class="col-md-6 cbi-save-btn-flex"  style="padding-left:0">
								<div class="cen-box">
								  {{ Form::submit('Update', array('class' => 'btn green')) }}
								</div>
                             </div>
							 <div class="col-md-6"></div>
						</div>
						{{ Form::close() }}
                    </div>

            </div>
         </div>
    </div>
</div>
@endsection