@extends('admin.layout.slave')

@section('master-main-content')
	<main class="container-main">
		<div class="panel-title">
			<h1>Leads</h1>
			<div class="primary-actions">
				<a href="{{ route('admin::lead.grab') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Grab Lead</a>
			</div>
			<div class="clearfix"></div>
		</div>

		<div class="panel-content">
			<div class="component-datatable">
				{!!
				$datatable->columns([
					'name'         => '<div class="cell-label orderable searchable">Lead name</div>' .
						Form::text('searchable[name]', null, ['class' => 'text-field', 'placeholder' => 'Search name']) .
						Form::hidden('orderable[name]'),
					'email'        => '<div class="cell-label orderable searchable">Email</div>' .
						Form::text('searchable[email]', null, ['class' => 'text-field', 'placeholder' => 'Search email']) .
						Form::hidden('orderable[email]'),
					'phone'        => '<div class="cell-label orderable searchable">Phone</div>' .
						Form::text('searchable[phone]', null, ['class' => 'text-field', 'placeholder' => 'Search phone']) .
						Form::hidden('orderable[phone]'),
					'status'       => '<div class="cell-label orderable searchable">Status</div>' .
						Form::select('searchable[status]', $statuses->lists('name', 'name')) .
						Form::hidden('orderable[status]'),
					'next_contact' => '<div class="cell-label orderable">Next contact</div>' .
						Form::hidden('orderable[next_contact_at]'),
					'actions'      => '<div class="cell-label">&nbsp;</div>',
				])
				->attributes([
					'id' => 'table-leads',
					'class' => 'table',
				])
				->render('components.datatable');
				!!}
			</div>
		</div>
	</main>
@endsection

@section('master-custom-css')
@endsection

@section('master-custom-js')
<script>
	jQuery(function($) {
		$('div.component-datatable').datatable({
			dataset: {!! $leads->toJson() !!},
			datasetKey: 'leads',
			row: function(item) {
				return [
					$(item).nested('name'),
					$(item).nested('email'),
					$(item).nested('phone'),
					$(item).nested('status'),
					$(item).nested('next_contact_at', '--'),
					'<a class="button" href="' + '/admin/leads/' + $(item).nested('id') + '"><i class="fa fa-external-link"></i></a></div>'
				];
			},
			modifyRow: function(item) {
				var classes = [
					'lead-item'
				];
				if($(item).nested('status') == 'new') {
					classes.push('lead-new');
				}
				if($(item).nested('status') == 'cold') {
					classes.push('lead-cold');
				}
				return {
					class: classes.join(' ')
				};
			}
		});
	});
</script>
@endsection