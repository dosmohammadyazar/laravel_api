@extends('layouts.app')
@section('title', 'Add Testimonial')
@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN VALIDATION STATES-->
        <div class="portlet light portlet-fit portlet-form bordered cbi-content-wrap">
			 <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject font-red sbold uppercase"><img src="{{ URL::asset('assets/images/Current-plan.png') }}"> Testimonial</span>
                    <h4 class="bas-title">Add Testimonial</h4>
                    <a href="/testimonial/" class="cbi-back-btn btn"><div class="btn-span"><img src="{{ URL::asset('assets/images/back-btn-icon.png') }}" alt="without-hover" class="without-hover">
                    <img src="{{ URL::asset('assets/images/back-btn-icon-hover.png') }}" alt="with-hover" class="with-hover"></div>Back</a>
                </div>
            </div>
             <div class="portlet-body edit-form">

                    {{ Form::open(['route' => 'testimonial.store',"class" => "form-horizontal"]) }}

					<div class="form-group">
            <div class="col-md-6">
              <label class="label-title">Title<span class="required"> * </span> </label>
              {{ Form::text('title', '', array('class' => 'form-control')) }}
              
                          @if ($errors->has('title'))
                              <div class="error">{{ $errors->first('title') }}</div>
                          @endif
            </div>
						<div class="col-md-6">
							<label class="label-title">Message<span class="required"> * </span> </label>
              {{ Form::textarea('message', '', array('class' => 'form-control' ,'id'=>'elm1','autocomplete'=>'off')) }}
							
                          @if ($errors->has('message'))
                              <div class="error">{{ $errors->first('message') }}</div>
                          @endif
						</div>
							
            </div>
            
           
					<div class="form-actions cbi-save-btn-wrap">
                        <div class="row">
                            <div class="col-md-6 cbi-save-btn-flex" style="padding-left:0">
								<div class="cen-box">
								  {{ Form::submit('Save', array('class' => 'btn green')) }}
								</div>
                             </div>
							 <div class="col-md-3"></div>
						</div>
						{{ Form::close() }}
                    </div>


            </div>
         </div>
    </div>
</div>

@endsection




