@extends('../app')
@section('content')
@if(is_null($configuration->extra_data))
<?php $configuration->extra_data = '[]'; ?>
@else
<?php

// echo '<pre>';
// print_r($configuration);
// exit;

foreach(json_decode($configuration->dynamic_data) as $dynData)
{
	if(is_numeric($dynData))
	{
		$dynamic_array[] = $dynData;
	}
}
?>
@endif
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>

<div class="row">
	<div class="col-md-12">
		<!-- Header -->
		<div class="panel panel-default">
			<div class="panel panel-default panel-header">
				<h4>Create Template</h4>
			</div><!-- End of panel-header -->
			<div class="panel panel-default panel-body">
				<form class="form-horizontal validate" action="{{ action('Client\InternalTemplateController@store') }}" method="POST" id="templateForm">
				<input type="hidden" name="id" value="{{ $configuration->id }}" />
				{!! Form::token() !!}

					<div class="form-group">
						<label for="name" class="col-md-3 text-right">Name</label>
						<div class="col-md-6">
							<input type="text" class="form-control required" name="name" data-validate='required charcheck' value="{{ $configuration->name }}" data-rule-charcheck="charcheck"/>
						</div>
					</div><!-- End of Name -->

					<div class="form-group">
						<label for="description" class="col-md-3 text-right">Description</label>
						<div class="col-md-6">
							<textarea class="form-control" name="description" rows="8">{{ $configuration->description }}</textarea>
						</div>
					</div><!-- End of Description -->

					<div class="form-group">
						<label for="rows" class="col-md-3 text-right">Total Rows</label>
						<div class="col-md-6">
							<select class="form-control required" name="column_count" data-validate="required">
								<option>--Select--</option>
								@for($count = 1; $count <= 100; $count++)
									@if($configuration->column_count == $count)
										<option value="{{ $count }}" selected>{{ $count }}</option>
									@else
										<option value="{{ $count }}">{{ $count }}</option>
									@endif
								@endfor
							</select>
						</div>
					</div><!-- End of Total Rows -->

					<div class="form-group  @if(is_null($configuration->id))colhide @endif">
						<label for="colHeaders" class="col-md-3 text-right">XLSX Headers</label>
						<div class="col-md-6">
							<p class="header-error"></p>
							<input type="text" class="form-control required" placeholder="Seperate the headers with a ," name="colHeaders" value="@if(!is_null($configuration->xlsx_headers)){{ implode(',',$configuration->xlsx_headers) }} @endif"/>
						</div>
					</div>

					<div class="form-group  @if(is_null($configuration->id))colhide @endif">
						<label for="static_data" class="col-md-3 text-right">Static Data</label>
						<div class="col-md-6">
							<select class="form-control select2 required" name="static_data[]" multiple data-tags="true" data-close-on-select="false" id="staticData">
								@if(!is_null($configuration->id))
									@for($count = 0; $count < $configuration->column_count; $count++ )
									@if(!in_array($count,$dynamic_array) && !in_array($count,json_decode($configuration->extra_data)))
										@if(in_array($count,json_decode($configuration->static_data)))
											<option value="{{ $count }}" selected>Row {{ $count }}</option>
										@else
											<option value="{{ $count }}">Row {{ $count }}</option>
										@endif
									@endif
									@endfor
								@endif
							</select>
						</div>
					</div><!-- End of Static Data -->

					<div class="form-group  @if(is_null($configuration->id))colhide @endif">
						<label for="dynamic_data" class="col-md-3 text-right">Dynamic Data</label>
						<div class="col-md-6">
							<select class="form-control select2 required" name="dynamic_data[]" multiple data-tags="true" data-close-on-select="false" id="dynamicData">
								@if(!is_null($configuration->id))
									@for($count = 0; $count < $configuration->column_count; $count++ )
										@if(!in_array($count,json_decode($configuration->static_data)) && !in_array($count,json_decode($configuration->extra_data)))
											@if(in_array($count,$dynamic_array))
												<option value="{{ $count }}" selected>Row {{ $count }}</option>
											@else
												<option value="{{ $count }}">Row {{ $count }}</option>
											@endif
										@endif
									@endfor
								@endif
							</select>
						</div>

						<div class="col-md-2 test-class1">
						<?php
								$common_rules = array();
						?>
						@if(!is_null($configuration->id))
							<?php
								$preCount = 0;
								$offset = count(json_decode($configuration->dynamic_data));
								$offset = (int)$offset;
								$common_rules = json_decode($configuration->generic_rules);
								if(is_null($common_rules))
								{
									$common_rules = array();
								}
							?>
						@endif

							<select class="form-control test-class1 select2" name="common_rules[]" multiple data-tags="true" data-close-on-select="false" data-placeholder="General rules">
							@if(!is_null($configuration) && !in_array('strong',$common_rules))
								<option value="strong">Strong</option>
							@else
								<option value="strong" selected> Strong </option>
							@endif

							@if(!is_null($configuration) && !in_array('h1',$common_rules))
								<option value="h1">H1</option>
							@else
								<option value="h1" selected> H1 </option>
							@endif

							@if(!is_null($configuration) && !in_array('h2',$common_rules))
								<option value="h2">H2</option>
							@else
								<option value="h2" selected> H2 </option>
							@endif

							@if(!is_null($configuration) && !in_array('h3',$common_rules))
								<option value="h3">H3</option>
							@else
								<option value="h3" selected> H3 </option>
							@endif

							@if(!is_null($configuration) && !in_array('h4',$common_rules))
								<option value="h4">H4</option>
							@else
								<option value="h4" selected> H4 </option>
							@endif

							@if(!is_null($configuration) && !in_array('paragraph',$common_rules))
								<option value="paragraph">Paragraph</option>
							@else
								<option value="paragraph" selected> Paragraph </option>
							@endif
							@if(!is_null($configuration) && !in_array('list',$common_rules))
								<option value="list">List</option>
							@else
								<option value="list" selected> List </option>
							@endif

							@if(!is_null($configuration) && !in_array('link',$common_rules))
								<option value="link">Link</option>
							@else
								<option value="link" selected> Link </option>
							@endif

							@if(!is_null($configuration) && !in_array('bold',$common_rules))
								<option value="bold">Step Bold</option>
							@else
								<option value="bold" selected> Step Bold </option>
							@endif

							@if(!is_null($configuration) && !in_array('bullet',$common_rules))
								<option value="bullet">Step Bullet</option>
							@else
								<option value="bullet" selected> Step Bullet </option>
							@endif
							@if(!is_null($configuration) && !in_array('Italic',$common_rules))
								<option value="italic">Italic</option>
							@else
								<option value="italic" selected> Italic </option>
							@endif
							@if(!is_null($configuration) && !in_array('linkNewTab',$common_rules))
								<option value="linkNewTab">Step URL</option>
							@else
								<option value="linkNewTab" selected> Step URL</option>
							@endif
							@if(!is_null($configuration) && !in_array('bbcode_strong',$common_rules))
								<option value="bbcode_strong">BB Code - Strong</option>
							@else
								<option value="bbcode_strong" selected>BB Code - Strong</option>
							@endif
							@if(!is_null($configuration) && !in_array('bbcode_bullet',$common_rules))
								<option value="bbcode_bullet">BB Code - Bullet</option>
							@else
								<option value="bbcode_bullet" selected>BB Code - Bullet</option>
							@endif
							@if(!is_null($configuration) && !in_array('bbcode_link',$common_rules))
								<option value="bbcode_link">BB Code - Link</option>
							@else
								<option value="bbcode_link" selected>BB Code - Link</option>
							@endif
							@if(!is_null($configuration) && !in_array('bbcode_h1',$common_rules))
								<option value="bbcode_h1">BB Code - H1</option>
							@else
								<option value="bbcode_h1" selected>BB Code - H1</option>
							@endif
							@if(!is_null($configuration) && !in_array('bbcode_h2',$common_rules))
								<option value="bbcode_h2">BB Code - H2</option>
							@else
								<option value="bbcode_h2" selected>BB Code - H2</option>
							@endif
							@if(!is_null($configuration) && !in_array('bbcode_h3',$common_rules))
								<option value="bbcode_h3">BB Code - H3</option>
							@else
								<option value="bbcode_h3" selected>BB Code - H3</option>
							@endif
							@if(!is_null($configuration) && !in_array('bbcode_h4',$common_rules))
								<option value="bbcode_h4">BB Code - H4</option>
							@else
								<option value="bbcode_h4" selected>BB Code - H4</option>
							@endif
							@if(!is_null($configuration) && !in_array('bbcode_paragraph',$common_rules))
								<option value="bbcode_paragraph">BB Code - Paragraph</option>
							@else
								<option value="bbcode_paragraph" selected>BB Code - Paragraph</option>
							@endif
							@if(!is_null($configuration) && !in_array('b_bold',$common_rules))
								<option value="b_bold">Bold (b)</option>
							@else
								<option value="b_bold" selected>Bold (b)</option>
							@endif
							@if(!is_null($configuration) && !in_array('paragraph_with_break',$common_rules))
								<option value="paragraph_with_break">Paragraph with break</option>
							@else
								<option value="paragraph_with_break" selected>Paragraph with break</option>
							@endif
							@if(!is_null($configuration) && !in_array('break',$common_rules))
								<option value="break">Break</option>
							@else
								<option value="break" selected>Break</option>
							@endif
							@if(!is_null($configuration) && !in_array('list_ol',$common_rules))
								<option value="list_ol">OL Bullet Point</option>
							@else
								<option value="list_ol" selected>OL Bullet Point</option>
							@endif
							@if(!is_null($configuration) && !in_array('bullet_underscore',$common_rules))
								<option value="bullet_underscore">Bullet Underscore</option>
							@else
								<option value="bullet_underscore" selected>Bullet Underscore</option>
							@endif
							</select>
						</div>
					</div><!-- End of Dynamic Data -->

					<div class="row pre-post-selector" id="pre-post-selector">
					<?php
						$dynamic_data = json_decode($configuration->dynamic_data);
						$dynamicCount = 1;
					?>
					@if(!is_null($dynamic_data))
					@foreach($dynamic_data as $dynamic)
						@if(is_numeric($dynamic))
							<?php $dynamicCount = $dynamicCount + 1; ?>
						@endif
					@endforeach
					@endif
						@if(!is_null($configuration->id))
							<?php
								$preCount = 0;
								$offset = count($dynamic_data)/4;
								$offset = (int)$offset;

							?>

							@for($count = 0; $count < $configuration->column_count; $count++ )

								@if(in_array($count,$dynamic_array))

									<div class="form-group">
										<label class="col-md-3"> </label>
										<div class="col-md-2">
											<input type="text" name="pre[{{ $preCount }}]" class="form-control col-md-3 pre" value="{{ $dynamic_data[$preCount] }}" placeholder="HTML tags"/>
										</div>
										<div class="col-md-2">
											Row {{ $count }}
										</div>
										<div class="col-md-2">
											<input type="text" name="post[]" class="form-control col-md-3" value="{{ $dynamic_data[$preCount+($offset*2)] }}" placeholder="HTML tags" />
										</div>
										<div class="col-md-2 test-class223">
											<select name="option[{{ $preCount }}][]" class="form-control select2" multiple data-tags="true" data-close-on-select="false">
												<option value="">--Select--</option>
	
												@if(in_array('h1',$dynamic_data[($preCount+($offset*3))]) && !in_array('h1',$common_rules))
													<option value="h1" selected>H1</option>
												@else
													<option value="h1" >H1</option>
												@endif
												@if(in_array('h2',$dynamic_data[($preCount+($offset*3))]) && !in_array('h2',$common_rules))
													<option value="h2" selected>H2</option>
												@else
													<option value="h2" >H2</option>
												@endif
												@if(in_array('h3',$dynamic_data[($preCount+($offset*3))]) && !in_array('h3',$common_rules))
													<option value="h3" selected>H3</option>
												@else
													<option value="h3" >H3</option>
												@endif
												@if(in_array('h4',$dynamic_data[($preCount+($offset*3))])&& !in_array('h4',$common_rules))
													<option value="h4" selected>H4</option>
												@else
													<option value="h4" >H4</option>
												@endif
												@if(in_array('strong',$dynamic_data[($preCount+($offset*3))])&& !in_array('strong',$common_rules))
													<option value="strong" selected>Strong</option>
												@else
													<option value="strong" >Strong</option>
												@endif
												@if(in_array('paragraph',$dynamic_data[($preCount+($offset*3))])&& !in_array('paragraph',$common_rules))
													<option value="paragraph" selected>Paragraph</option>
												@else
													<option value="paragraph" >Paragraph</option>
												@endif
												@if(in_array('list',$dynamic_data[($preCount+($offset*3))])&& !in_array('list',$common_rules))
													<option value="list" selected>List</option>
												@else
													<option value="list" >List</option>
												@endif
												@if(in_array('link',$dynamic_data[($preCount+($offset*3))])&& !in_array('link',$common_rules))
													<option value="link" selected>Link</option>
												@else
													<option value="link" >Link</option>
												@endif
												@if(in_array('bold',$dynamic_data[($preCount+($offset*3))])&& !in_array('bink',$common_rules))
													<option value="bold" selected>Step Bold</option>
												@else
													<option value="bold" >Step Bold</option>
												@endif
												@if(in_array('bullet',$dynamic_data[($preCount+($offset*3))])&& !in_array('bullet',$common_rules))
													<option value="bullet" selected>Step Bullet</option>
												@else
													<option value="bullet" >Step Bullet</option>
												@endif
												@if(in_array('linkNewTab',$dynamic_data[($preCount+($offset*3))])&& !in_array('linkNewTab',$common_rules))
													<option value="linkNewTab" selected>Step URL </option>
												@else
													<option value="linkNewTab" >Step URL </option>
												@endif
												@if(in_array('italic',$dynamic_data[($preCount+($offset*3))])&& !in_array('italic',$common_rules))
													<option value="italic" selected>Italic</option>
												@else
													<option value="italic" >Italic</option>
												@endif
												@if(in_array('bbcode_strong',$dynamic_data[($preCount+($offset*3))])&& !in_array('bbcode_strong',$common_rules))
													<option value="bbcode_strong" selected>BB Code - Strong</option>
												@else
													<option value="bbcode_strong" >BB Code - Strong</option>
												@endif
												@if(in_array('bbcode_bullet',$dynamic_data[($preCount+($offset*3))])&& !in_array('bbcode_bullet',$common_rules))
													<option value="bbcode_bullet" selected>BB Code - Bullet</option>
												@else
													<option value="bbcode_bullet">BB Code - Bullet</option>
												@endif
												@if(in_array('bbcode_link',$dynamic_data[($preCount+($offset*3))])&& !in_array('bbcode_link',$common_rules))
													<option value="bbcode_link" selected>BB Code - Link</option>
												@else
													<option value="bbcode_link">BB Code - Link</option>
												@endif
												@if(in_array('bbcode_h1',$dynamic_data[($preCount+($offset*3))])&& !in_array('bbcode_h1',$common_rules))
													<option value="bbcode_h1" selected>BB Code - H1</option>
												@else
													<option value="bbcode_h1">BB Code - H1</option>
												@endif
												@if(in_array('bbcode_h2',$dynamic_data[($preCount+($offset*3))])&& !in_array('bbcode_h2',$common_rules))
													<option value="bbcode_h2" selected>BB Code - H2</option>
												@else
													<option value="bbcode_h2">BB Code - H2</option>
												@endif
												@if(in_array('bbcode_h3',$dynamic_data[($preCount+($offset*3))])&& !in_array('bbcode_h3',$common_rules))
													<option value="bbcode_h3" selected>BB Code - H3</option>
												@else
													<option value="bbcode_h3">BB Code - H3</option>
												@endif
												@if(in_array('bbcode_h4',$dynamic_data[($preCount+($offset*3))])&& !in_array('bbcode_h4',$common_rules))
													<option value="bbcode_h4" selected>BB Code - H4</option>
												@else
													<option value="bbcode_h4">BB Code - H4</option>
												@endif
												@if(in_array('bbcode_paragraph',$dynamic_data[($preCount+($offset*3))])&& !in_array('bbcode_h4',$common_rules))
													<option value="bbcode_paragraph" selected>BB Code - Paragraph</option>
												@else
													<option value="bbcode_paragraph">BB Code - Paragraph</option>
												@endif
												@if(in_array('b_bold',$dynamic_data[($preCount+($offset*3))])&& !in_array('b_bold',$common_rules))
													<option value="b_bold" selected>Bold (b)</option>
												@else
													<option value="b_bold">Bold (b)</option>
												@endif
												@if(in_array('paragraph_with_break',$dynamic_data[($preCount+($offset*3))])&& !in_array('paragraph_with_break',$common_rules))
													<option value="paragraph_with_break" selected>Paragraph with break</option>
												@else
													<option value="paragraph_with_break">Paragraph with break</option>
												@endif
												@if(in_array('break',$dynamic_data[($preCount+($offset*3))])&& !in_array('break',$common_rules))
													<option value="break" selected>Break</option>
												@else
													<option value="break">Break</option>
												@endif
												@if(in_array('list_ol',$dynamic_data[($preCount+($offset*3))])&& !in_array('list_ol',$common_rules))
													<option value="list_ol" selected>OL Bullet Point</option>
												@else
													<option value="list_ol">OL Bullet Point</option>
												@endif
												@if(in_array('bullet_underscore',$dynamic_data[($preCount+($offset*3))])&& !in_array('bullet_underscore',$common_rules))
													<option value="bullet_underscore" selected>Bullet Underscore</option>
												@else
													<option value="bullet_underscore">Bullet Underscore</option>
												@endif
											</select>
										</div>
									</div>
								<?php $preCount = $preCount+1; ?>
								@endif
							@endfor
						@endif
					</div>

					<div class="choose-header">
						<div class="form-group  @if(is_null($configuration->id))colhide @endif">
							<label for="choose_header" class="col-md-3 text-right">Choose header</label>
							<div class="col-md-6">
								<select class="form-control select2 required" name="choose_header[]" data-tags="true" data-close-on-select="false" id="chooseHeader">
									@if(!is_null($configuration->id))
										@for($count = 0; $count < $configuration->column_count; $count++ )

										@if(!in_array($count,$dynamic_array) && !in_array($count,json_decode($configuration->extra_data)))

											@if(in_array($count,json_decode($configuration->choose_header)))
												<option value="{{ $count }}" selected>Row {{ $count }}</option>
											@else
												<option value="{{ $count }}">Row {{ $count }}</option>
											@endif

										@endif

										@endfor
									@endif
								</select>
							</div>
						</div><!-- End of Choose header -->
					</div>

					<div class="form-group  @if(is_null($configuration->id))colhide @endif">
						<label for="filename" class="col-md-3 text-right">Filename</label>
						<div class="col-md-6">
							<?php 
								$filenames = array();
								$fname = json_decode($configuration->filename);
								if(is_array($fname) || is_object($fname)){
									foreach($fname as $key => $value){
										if(is_numeric($value)){
											$filenames[] = $value;
										}
									}
									// echo $column_count = $configuration->column_count;
									// echo '<pre>';
									// print_r($column_count);
									// exit();
									// print_r($filenames);
								}
								
							?>
							<select class="form-control select2 required" name="filename[]" multiple data-tags="true" data-close-on-select="false" id="fileName">
								
								@if(!is_null($configuration->id))

									@foreach ($filenames as $key => $value)
										$newarray[] = $value;
										<option value="{{ $key }}" selected>Row {{ $value }}</option> 
									@endforeach

									<!-- @for($count = 0; $count < $configuration->column_count; $count++ )
										@if(in_array($count,$filenames))
											<option value="{{ $count }}" selected>Row {{ $count }}</option>
										@else
											<option value="{{ $count }}">Row {{ $count }}</option>
										@endif
									@endfor --> 

								@endif

							</select>
						</div>
					</div>

					<div class="form-group  @if(is_null($configuration->id))colhide @endif">
						<label for="delivery_data2" class="col-md-3 text-right">Delivery Data</label>
						<div class="col-md-6">
							<select class="form-control select2 required" name="delivery_data2[]" multiple data-tags="true" data-close-on-select="false" id="deliveryData2">
								@if(!is_null($configuration->id))
									@for($count = 0; $count < $configuration->column_count; $count++ )
										@if(!in_array($count,json_decode($configuration->static_data)) && !in_array($count,json_decode($configuration->extra_data)))
											@if(in_array($count,$dynamic_array))
												<option value="{{ $count }}" selected>Row {{ $count }}</option>
											@else
												<option value="{{ $count }}">Row {{ $count }}</option>
											@endif
										@endif
									@endfor
								@endif
							</select>
						</div>
					</div>

					<div class="form-group  @if(is_null($configuration->id))colhide @endif">
						<label for="extra_data" class="col-md-3 text-right" id="extraData">Extra Data</label>
						<div class="col-md-6">
							<select class="form-control select2" name="extra_data[]" multiple data-tags="true" data-close-on-select="false">
								@if(!is_null($configuration->id) && !is_null($configuration->extra_data))
									@for($count = 0; $count < $configuration->column_count; $count++ )

										@if(!in_array($count,json_decode($configuration->static_data)) && !in_array($count,json_decode($configuration->dynamic_data)))
										@if(in_array($count,json_decode($configuration->extra_data)))
											<option value="{{ $count }}" selected>Row {{ $count }}</option>
										@else
											<option value="{{ $count }}">Row {{ $count }}</option>
										@endif
										@endif
									@endfor
								@endif
							</select>
						</div>
					</div><!-- End of Extra Data -->
					
					<div class="form-group @if(is_null($configuration->id))colhide @endif">
						<label class="col-md-3">Important Data(1)</label>
						<div class="col-md-6">
							<select class="form-control" name="imp1_data">
								@if(!is_null($configuration->id))
									<option value="">--Select--</option>
									@for($count = 0; $count < $configuration->column_count; $count++ )
										@if($count == $configuration->imp1_data && $configuration->imp1_data!= '')
											<option value="{{ $count }}" selected>Row {{ $count }}</option>
										@else
											<option value="{{ $count }}">Row {{ $count }}</option>

										@endif
									@endfor
								@endif
							</select>
						</div>
					</div><!-- End of Important Data 1 -->

					<div class="form-group  @if(is_null($configuration->id))colhide @endif">
						<label class="col-md-3">Important Data(2)</label>
						<div class="col-md-6">
							<select class="form-control" name="imp2_data">
								@if(!is_null($configuration->id))
									<option value="">--Select--</option>
									@for($count = 0; $count < $configuration->column_count; $count++ )
										@if($count == $configuration->imp2_data && $configuration->imp2_data!= '')
											<option value="{{ $count }}" selected>Row {{ $count }}</option>
										@else
											<option value="{{ $count }}">Row {{ $count }}</option>

										@endif
									@endfor
								@endif
							</select>
						</div>
					</div><!-- End of Important Data 2 -->

					<div class="form-group  @if(is_null($configuration->id))colhide @endif">
						<label class="col-md-3">Important Data(3)</label>
						<div class="col-md-6">
							<select class="form-control" name="imp3_data">
								@if(!is_null($configuration->id))
									<option value="">--Select--</option>
									@for($count = 1; $count <= $configuration->column_count; $count++ )
										@if($count == $configuration->imp3_data && $configuration->imp3_data!= '')
											<option value="{{ $count }}" selected>Row {{ $count }}</option>
										@endif
									@endfor
								@endif
							</select>
						</div>
					</div><!-- End of Important Data 3 -->

				</form>
			</div><!-- End of panel body -->
			<div class="panel panel-default panel-footer">
				<div class="pull-right">
					<button type="button" class="btn btn-info btn-md" id="template_preview" data-toggle="modal" data-target="#previewModal">Preview</button>
					<button type="button" class="btn btn-success btn-md" id="templateFormSubmit">Save</button>
				</div>
			</div>
		</div><!-- End of panel -->
	</div>
</div>

<!-- Preview modal -->
<div id="previewModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Preview</h4>
      </div>
      <div class="modal-body">
        	<div class="preview-generate">

        	</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div><!-- End of preview modal -->
<script src="{{ asset('/assets/js/jquery-validate/jquery.validate.min.js') }}"></script>
<script>
	$(document).ready(function()
	{

		$('.colhide').hide();
		//Save the create/update form
		$('#templateFormSubmit').click(function()
		{
			var xlsxHeaders = $('[name="colHeaders"]').val().split(',');
			if(xlsxHeaders.length != $('[name="column_count"]').val())
			{
				$('.header-error').html('<strong style="color:red">The number of headers should be equal to the selected Total Rows</strong>');
				return false;
			}
		});

		$('[name="column_count"]').change(function()
		{
			$('.colhide').show(300);
			$('.select2').css('width','100%');
			var column_count = $('[name="column_count"]').val();
			var choose_header = $('[name="choose_header"]').val();
			var dynamic_data = $('[name="dynamic_data"]').val();
			var delivery_data = $('[name="delivery_data"]').val();
			var delivery_data2 = $('[name="delivery_data2"]').val();
			var filename = $('[name="filename"]').val();
			var extra_data = $('[name="extra_data"]').val();

			if(dynamic_data == null)
			{
				dynamic_data = new Array();
			}
			if(choose_header == null)
			{
				choose_header = new Array();
			}
			if(delivery_data == null)
			{
				delivery_data = new Array();
			}
			if(delivery_data2 == null)
			{
				delivery_data2 = new Array();
			}
			if(filename == null)
			{
				filename = new Array();
			}
			if(extra_data == null)
			{
				extra_data = new Array();
			}
			// Reset Static, Dynamic and Extra data
			$('[name="static_data[]"]').find('option').remove();
			$('[name="choose_header[]"]').find('option').remove();
			$('[name="dynamic_data[]"]').find('option').remove();
			$('[name="delivery_data[]"]').find('option').remove();
			$('[name="delivery_data2[]"]').find('option').remove();
			$('[name="filename[]"]').find('option').remove();
			$('[name="extra_data[]"]').find('option').remove();

			//$('[name="imp1_data"]').append('<option value=""> --Select --</option>');
			// Add the available options
			for(var count = 0; count < column_count; count++)
			{
				if(jQuery.inArray(count,dynamic_data) == -1 && jQuery.inArray(count,extra_data) == -1)
				{
					$('[name="static_data[]"]').append('<option value="'+count+'"> Row '+count+'</option>');
					$('[name="choose_header[]"]').append('<option value="'+count+'"> Row '+count+'</option>');
					$('[name="dynamic_data[]"]').append('<option value="'+count+'"> Row '+count+'</option>');
					$('[name="delivery_data[]"]').append('<option value="'+count+'"> Row '+count+'</option>');
					$('[name="delivery_data2[]"]').append('<option value="'+count+'"> Row '+count+'</option>');
					$('[name="filename[]"]').append('<option value="'+count+'"> Row '+count+'</option>');
				}
					$('[name="imp1_data"]').append('<option value="'+count+'"> Row '+count+'</option>');

			}
		});

		$('[name="static_data[]"]').on('change',function(){

			var column_count = $('[name="column_count"]').val();
			var static_data = $('[name="static_data[]"]').val();
			var choose_header = $('[name="choose_header[]"]').val();
			var dynamic_data = $('[name="dynamic_data[]"]').val();
			var delivery_data = $('[name="delivery_data[]"]').val();
			// var delivery_data2 = $('[name="delivery_data2[]"]').val();
			var extra_data = $('[name="extra_data[]"]').val();

			if(static_data == null)
			{
				static_data = new Array();
			}
			if(choose_header == null)
			{
				choose_header = new Array();
			}
			if(delivery_data == null)
			{
				delivery_data = new Array();
			}
			if(extra_data == null)
			{
				extra_data = new Array();
			}

			//$('[name="dynamic_data[]"]').find('option').remove();
			$('[name="extra_data[]"]').find('option').remove();
			for(var count = 0; count < column_count; count++)
			{
				if(static_data.indexOf(""+count) != -1)
				{

					//$('[name="dynamic_data[]"]').append('<option value="'+count+'"> Row '+count+'</option>');
					$('select#dynamicData').find('option[value="'+count+'"]').remove();
					// $('select#deliveryData2').find('option[value="'+count+'"]').remove();
				}
				if((static_data.indexOf(""+count) == -1 && !$('select#dynamicData').find('option[value="'+count+'"]').length))
				{
					$('select#dynamicData').append('<option value="'+count+'"> Row '+count+'</option>');
				}
				if((static_data.indexOf(""+count) == -1 && !$('select#deliveryData2').find('option[value="'+count+'"]').length))
				{
					$('select#deliveryData2').append('<option value="'+count+'"> Row '+count+'</option>');
				}
				if((static_data.indexOf(""+count) == -1 && !$('select#fileName').find('option[value="'+count+'"]').length))
				{
					$('select#fileName').append('<option value="'+count+'"> Row '+count+'</option>');
				}
			}
		});
		
		$('[name="choose_header[]"]').on('change',function(){

			var column_count = $('[name="column_count"]').val();
			var static_data = $('[name="static_data[]"]').val();
			var choose_header = $('[name="choose_header[]"]').val();
			var dynamic_data = $('[name="dynamic_data[]"]').val();
			var delivery_data = $('[name="delivery_data[]"]').val();
			// var delivery_data2 = $('[name="delivery_data2[]"]').val();
			var extra_data = $('[name="extra_data[]"]').val();

			if(static_data == null)
			{
				static_data = new Array();
			}
			if(choose_header == null)
			{
				choose_header = new Array();
			}
			if(delivery_data == null)
			{
				delivery_data = new Array();
			}
			if(extra_data == null)
			{
				extra_data = new Array();
			}

			//$('[name="dynamic_data[]"]').find('option').remove();
			$('[name="extra_data[]"]').find('option').remove();
			for(var count = 0; count < column_count; count++)
			{
				if(choose_header.indexOf(""+count) != -1)
				{

					//$('[name="dynamic_data[]"]').append('<option value="'+count+'"> Row '+count+'</option>');
					$('select#dynamicData').find('option[value="'+count+'"]').remove();
					// $('select#delivery_data2').find('option[value="'+count+'"]').remove();
				}
				if((choose_header.indexOf(""+count) == -1 && !$('select#dynamicData').find('option[value="'+count+'"]').length))
				{
					$('select#dynamicData').append('<option value="'+count+'"> Row '+count+'</option>');
				}
				// if((choose_header.indexOf(""+count) == -1 && !$('select#delivery_data2').find('option[value="'+count+'"]').length))
				// {
				// 	$('select#delivery_data2').append('<option value="'+count+'"> Row '+count+'</option>');
				// }

			}
		});

		$('[name="delivery_data[]"]').on('change',function(){

			var column_count = $('[name="column_count"]').val();
			var static_data = $('[name="static_data[]"]').val();
			var choose_header = $('[name="choose_header[]"]').val();
			var dynamic_data = $('[name="dynamic_data[]"]').val();
			var delivery_data = $('[name="delivery_data[]"]').val();
			var extra_data = $('[name="extra_data[]"]').val();

			if(static_data == null)
			{
				static_data = new Array();
			}
			if(choose_header == null)
			{
				choose_header = new Array();
			}
			if(delivery_data == null)
			{
				delivery_data = new Array();
			}
			if(extra_data == null)
			{
				extra_data = new Array();
			}

			//$('[name="dynamic_data[]"]').find('option').remove();
			$('[name="extra_data[]"]').find('option').remove();
			for(var count = 0; count < column_count; count++)
			{
				if(delivery_data.indexOf(""+count) != -1)
				{

					//$('[name="dynamic_data[]"]').append('<option value="'+count+'"> Row '+count+'</option>');
					$('select#dynamicData').find('option[value="'+count+'"]').remove();
				}
				if((delivery_data.indexOf(""+count) == -1 && !$('select#dynamicData').find('option[value="'+count+'"]').length))
				{
					$('select#dynamicData').append('<option value="'+count+'"> Row '+count+'</option>');
				}
			}
		});/**/

		$('[name="dynamic_data[]"]').on('change',function(e){

			var column_count = $('[name="column_count"]').val();
			var static_data = $('[name="static_data[]"]').val();
			var choose_header = $('[name="choose_header[]"]').val();
			var delivery_data = $('[name="delivery_data[]"]').val();
			var dynamic_data = $('[name="dynamic_data[]"]').val();

			if(static_data == null)
			{
				static_data = new Array();
			}
			if(choose_header == null)
			{
				choose_header = new Array();
			}
			if(delivery_data == null)
			{
				delivery_data = new Array();
			}
			if(dynamic_data == null)
			{
				dynamic_data = new Array();
			}
			//console.log(dynamic_data.indexOf("3"));
			$('[name="extra_data[]"]').find('option').remove();
			for(var count = 0; count < column_count; count++)
			{
				// console.log(dynamic_data.indexOf(""+count));
				if(dynamic_data.indexOf(""+count) != -1 )
				{
					//$('[name="extra_data[]"]').append('<option value="'+count+'"> Row '+count+'</option>');
					$('select#staticData').find('option[value="'+count+'"]').remove();
					$('select#chooseHeader').find('option[value="'+count+'"]').remove();
					$('select#deliveryData').find('option[value="'+count+'"]').remove();
				}

				if(static_data != null && (dynamic_data.indexOf(""+count) == -1 && !$('select#staticData').find('option[value="'+count+'"]').length))
				{
					$('select#staticData').append('<option value="'+count+'"> Row '+count+'</option>');
				}

				if(choose_header != null && (dynamic_data.indexOf(""+count) == -1 && !$('select#chooseHeader').find('option[value="'+count+'"]').length))
				{
					$('select#chooseHeader').append('<option value="'+count+'"> Row '+count+'</option>');
				}

				if(delivery_data != null && (dynamic_data.indexOf(""+count) == -1 && !$('select#deliveryData').find('option[value="'+count+'"]').length))
				{
					$('select#deliveryData').append('<option value="'+count+'"> Row '+count+'</option>');
				}
			}

			$('#pre-post-selector').empty();
			for(var data = 0; data < dynamic_data.length; data++)
			{
				$('#pre-post-selector').append('<div class="form-group"><label class="col-md-3"></label><div class="col-md-2"><input type="text" class="form-control pre" name="pre['+data+']" placeholder="HTML tags" /></div><div class="col-md-2">Row '+dynamic_data[data]+'</div><div class="col-md-2"><input type="text" class="form-control" name="post[]" placeholder="HTML tags" /></div><div class="col-md-2"><select name="option['+data+'][]" class="form-control select2" multiple><option value="">--Select--</option><option value="h1">H1</option><option value="h2">H2</option><option value="h3">H3</option><option value="h4">H4</option><option value="strong">Strong</option><option value="paragraph">Paragraph</option><option value="list">List</option><option value="link">Link</option><option value="bold" >Step Bold</option><option value="italic" >Italic</option><option value="bullet" >Step Bullet</option><option value="linkNewTab" >Step URL</option><option value="bbcode_strong">BBCode Strong</option><option value="bbcode_link">BBCode Link</option><option value="bbcode_list">BBCode list</option><option value="bbcode_bullet">BBCode Bullet</option><option value="bbcode_h1">BBCode H1</option><option value="bbcode_h2">BBCode H2</option><option value="bbcode_h3">BBCode H3</option><option value="bbcode_h4">BBCode H4</option><option value="bbcode_paragraph">BBCode paragraph</option><option value="b_bold">Bold (b)</option><option value="paragraph_with_break">Paragraph with break</option><option value="break">Break</option><option value="list_ol">OL Bullet Point</option><option value="bullet_underscore">Bullet Underscore</option></select></div></div>' );

		            $("#pre-post-selector > .form-group > .col-md-2 > select").select2({ });
			}
		});

		
		$('[name="delivery_data2[]"]').on('change',function(e){

			var column_count = $('[name="column_count"]').val();
			var static_data = $('[name="static_data[]"]').val();
			var choose_header = $('[name="choose_header[]"]').val();
			var delivery_data = $('[name="delivery_data[]"]').val();
			var dynamic_data = $('[name="dynamic_data[]"]').val();
			var delivery_data2 = $('[name="delivery_data2[]"]').val();

			if(static_data == null)
			{
				static_data = new Array();
			}
			if(choose_header == null)
			{
				choose_header = new Array();
			}
			if(delivery_data == null)
			{
				delivery_data = new Array();
			}
			if(dynamic_data == null)
			{
				dynamic_data = new Array();
			}
			if(delivery_data2 == null)
			{
				delivery_data2 = new Array();
			}			
			//console.log(dynamic_data.indexOf("3"));
			$('[name="extra_data[]"]').find('option').remove();
			for(var count = 0; count < column_count; count++)
			{
				// console.log(dynamic_data.indexOf(""+count));
				if(delivery_data2.indexOf(""+count) != -1 )
				{
					//$('[name="extra_data[]"]').append('<option value="'+count+'"> Row '+count+'</option>');
					// $('select#staticData').find('option[value="'+count+'"]').remove();
					// $('select#chooseHeader').find('option[value="'+count+'"]').remove(); 
					$('select#deliveryData').find('option[value="'+count+'"]').remove();
				}

				// if(static_data != null && (delivery_data2.indexOf(""+count) == -1 && !$('select#staticData').find('option[value="'+count+'"]').length))
				// {
				// 	$('select#staticData').append('<option value="'+count+'"> Row '+count+'</option>');
				// }

				// if(choose_header != null && (delivery_data2.indexOf(""+count) == -1 && !$('select#chooseHeader').find('option[value="'+count+'"]').length))
				// {
				// 	$('select#chooseHeader').append('<option value="'+count+'"> Row '+count+'</option>');
				// }

				// if(delivery_data != null && (delivery_data2.indexOf(""+count) == -1 && !$('select#deliveryData').find('option[value="'+count+'"]').length))
				// {
				// 	$('select#deliveryData').append('<option value="'+count+'"> Row '+count+'</option>');
				// }
			}

/*			$('#pre-post-selector').empty();
			for(var data = 0; data < delivery_data2.length; data++)
			{
				$('#pre-post-selector').append('<div class="form-group"><label class="col-md-3"></label><div class="col-md-2"><input type="text" class="form-control pre" name="pre['+data+']" placeholder="HTML tags" /></div><div class="col-md-2">Row '+delivery_data2[data]+'</div><div class="col-md-2"><input type="text" class="form-control" name="post[]" placeholder="HTML tags" /></div><div class="col-md-2"><select name="option['+data+'][]" class="form-control select2" multiple><option value="">--Select--</option><option value="h1">H1</option><option value="h2">H2</option><option value="h3">H3</option><option value="h4">H4</option><option value="strong">Strong</option><option value="paragraph">Paragraph</option><option value="list">List</option><option value="link">Link</option><option value="bold" >Step Bold</option><option value="italic" >Italic</option><option value="bullet" >Step Bullet</option><option value="linkNewTab" >Step URL</option><option value="bbcode_strong">BBCode Strong</option><option value="bbcode_link">BBCode Link</option><option value="bbcode_list">BBCode list</option><option value="bbcode_bullet">BBCode Bullet</option><option value="bbcode_h1">BBCode H1</option><option value="bbcode_h2">BBCode H2</option><option value="bbcode_h3">BBCode H3</option><option value="bbcode_h4">BBCode H4</option><option value="bbcode_paragraph">BBCode paragraph</option><option value="b_bold">Bold (b)</option><option value="paragraph_with_break">Paragraph with break</option><option value="break">Break</option><option value="list_ol">OL Bullet Point</option></select></div></div>' );

		            $("#pre-post-selector > .form-group > .col-md-2 > select").select2({ });
			}*/
		}); /**/

		$('[name="filename[]"]').on('change',function(e){

			var column_count = $('[name="column_count"]').val();
			var static_data = $('[name="static_data[]"]').val();
			var choose_header = $('[name="choose_header[]"]').val();
			var delivery_data = $('[name="delivery_data[]"]').val();
			var dynamic_data = $('[name="dynamic_data[]"]').val();
			var delivery_data2 = $('[name="delivery_data2[]"]').val();
			var filename = $('[name="filename[]"]').val();

			if(static_data == null)
			{
				static_data = new Array();
			}
			if(choose_header == null)
			{
				choose_header = new Array();
			}
			if(delivery_data == null)
			{
				delivery_data = new Array();
			}
			if(dynamic_data == null)
			{
				dynamic_data = new Array();
			}
			if(delivery_data2 == null)
			{
				delivery_data2 = new Array();
			}			
			if(filename == null)
			{
				filename = new Array();
			}			
			//console.log(dynamic_data.indexOf("3"));
			$('[name="extra_data[]"]').find('option').remove();
			for(var count = 0; count < column_count; count++)
			{
				// console.log(dynamic_data.indexOf(""+count));
				if(delivery_data2.indexOf(""+count) != -1 )
				{
					$('select#deliveryData').find('option[value="'+count+'"]').remove();
				}
				if(filename.indexOf(""+count) != -1 )
				{
					$('select#deliveryData').find('option[value="'+count+'"]').remove();
				}

			}

		});


		$('[name="imp1_data"]').on('change', function()
		{
			var column_count = $('[name="column_count"]').val();
			var imp1_data = $('[name="imp1_data"]').val();

			$('[name="imp2_data"]').find('option').remove();
			$('[name="imp2_data"]').append('<option value=""> -- Select -- </option>');
			for(var count = 0; count < column_count; count++)
			{
				if(imp1_data.indexOf(""+count) == -1)
				{

					$('[name="imp2_data"]').append('<option value="'+count+'"> Row '+count+'</option>');
				}

			}

		});

		$('[name="imp2_data"]').on('change', function()
		{
			var column_count = $('[name="column_count"]').val();
			var imp1_data = $('[name="imp1_data"]').val();
			var imp2_data = $('[name="imp2_data"]').val();

			$('[name="imp3_data"]').find('option').remove();
			$('[name="imp3_data"]').append('<option value=""> -- Select -- </option>');
			for(var count = 0; count < column_count; count++)
			{
				if( imp1_data.indexOf(""+count) == -1 && imp2_data.indexOf(""+count) == -1)
				{


					$('[name="imp3_data"]').append('<option value="'+count+'"> Row '+count+'</option>');
				}

			}

		});

		$("select").on("select2:select", function (evt) {
			var element = evt.params.data.element;
			var $element = $(element);

			$element.detach();
			$(this).append($element);
			$(this).trigger("change");
		});

	});

	/* Preview mode */
	$(document).ready(function()
	{
		var error = 0;
		$('#template_preview').click(function()
		{
			var html = '';
			var column_count = $('[name="column_count"]').val();
			var static_data = $('[name="static_data[]"]').val();
			var delivery_data = $('[name="delivery_data[]"]').val();
			var choose_header = $('[name="choose_header[]"]').val();
			var dynamic_data = $('[name="dynamic_data[]"]').val();
			var extra_data = $('[name="extra_data[]"]').val();
			var imp1_data = $('[name="imp1_data"]').val();
			var imp2_data = $('[name="imp2_data"]').val();
			var imp3_data = $('[name="imp3_data"]').val();
			var name = $('[name="name"]').val();

			if(name == null)
			{
				name = ' ';
			}
			var header = ' ';
			var body = '<tbody>';

			for(var count = 0; count < column_count; count++)
			{
				body = body+'<tr>';
				if(static_data.indexOf(""+count) != -1)
				{
					body = body+'<td style="background-color:#18A8DF">'+count+'</td><td style="background-color:#18A8DF">Static Data '+count+'</td><td style="background-color:#18A8DF"></td>';
				}
				else if(dynamic_data.indexOf(""+count) != -1)
				{
					if(count == 0)
					{
						body = body+'<td style="background-color:orange">'+count+'</td><td style="background-color:orange">Dynamic Data '+count+'</td><td>1234567890</td>';
					}
					else
					{
						body = body+'<td style="background-color:orange">'+count+'</td><td style="background-color:orange">Dynamic Data '+count+'</td><td></td>';
					}
				}

				body = body+'</tr>';
			}
			body = body+'</tbody>';
			$('.preview-generate').html('<table class="table preview-table table-bordered" >'+header+body+'</table>');
		});

		$('#templateFormSubmit').click(function()
		{
			$('#templateForm').submit();
		});

		$('select').on('change',function()
		{
			if($.inArray('break',$(this).val()) != -1)
			{
				$(this).find('option[value="paragraph"]').remove();
			}
			if($.inArray('paragraph',$(this).val()) != -1)
			{
				$(this).find('option[value="break"]').remove();
			}
		});

		$('form').submit(function()
		{
			var common_rules = [];
			var j = 0;
			$('[name="common_rules[]"]  option:selected').each(function() {
				console.log($(this).val());
			    common_rules[j] = $(this).val();
			    j = j+1;
			});

			for(var i = 0; i < $('.pre').length; i++)
			{
				var pre = $('[name="pre['+i+']"]').val();
				var option = [];
				var j = 0;
			    $('[name="option['+i+'][]"]  option:selected').each(function() {
			        option[j] = $(this).val();
			        j = j+1;
			    });
			    if((option.indexOf('strong') != -1 && pre == '<strong>') || (common_rules.indexOf('strong') != -1 && pre == '<strong>'))
			    {
			    	if(error == 0)
			    	{
			    		$('[name="pre['+i+']"').after('<div class="col-md-12" style="color:red">This column cannot be entirely "strong". Please change pre/post tags or the rules</div>');
			    	error = 1;
			    	}

			    	return false;
			    }

			    if((option.indexOf('italic') != -1 && pre == '<i>')|| (common_rules.indexOf('italic') != -1 && pre == '<i>'))
			    {
			    	if(error == 0)
			    	{
			    		$('[name="pre['+i+']"').after('<div class="col-md-12" style="color:red">This column cannot be entirely "italics". Please change pre/post tags or the rules</div>');
			    	error = 1;
			    	}

			    	return false;
			    }
			}
		});

		$('[name="colHeaders"]').on('blur',function()
		{
			var xlsxHeaders = $('[name="colHeaders"]').val().split(',');
			if(xlsxHeaders.length != $('[name="column_count"]').val())
			{
				$('.header-error').html('<strong style="color:red" class="heaer-error-msg">The number of headers should be equal to the selected Total Rows</strong>');
			}else{
				$('.header-error').empty();
			}
		});
	});

	$(document).ready(function(){
jQuery.validator.addMethod("charcheck", function(value, element) {
  var res =  /^[a-zA-Z0-9ÀàÂâÆæÇçÈèÉéÊêËëÎîÏïÔôŒœÙùÛûÜüńǸǹŇňñäóößúáÁÇÍÑðóíĳĲŸÿā _-]*$/.test(value);
  //console.log(res);
  return res;
}, "Special characters are not allowed");
	});
</script>
@endsection
