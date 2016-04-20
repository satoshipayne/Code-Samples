<li class="itemlist__item gig{{ $gig->hasExpired() ? ' --expired' : '' }} --avatar-display-{{ $hub->profile_picture_display }}" data-id="{{ $gig->id }}">
	<h3 class="gig__title"><a data-internal="true" href="{{ $gig->getDetailsUrl($hub) }}">{{ $gig->title }}</a></h3>
	@if(!is_null($gig->brand))
		<div class="gig__owner avatar">
			<a data-internal="true" href="{{ $gig->getDetailsUrl($hub) }}"><img src="{{ $gig->brand->profile_picture_small }}" alt="" /></a>
		</div>
	@else
		<div class="gig__owner avatar avatar__items --display-{{ $hub->profile_picture_display }}">
			<a data-internal="true" href="{{ $gig->getDetailsUrl($hub) }}"><img src="{{ $hub->profile_picture_small }}" alt="" /></a>
		</div>
	@endif
	@if($gig->hasExpired())
		<span class="gig__deadline"><i class="fa fa-clock-o"></i> Listing expired <time>{{ $gig->deadline_at_relative }}</time></span>
	@else
		<span class="gig__deadline"><i class="fa fa-clock-o"></i> Listing ends in <time>{{ $gig->deadline_at_relative }}</time></span>
	@endif
	<p class="gig__description
		@if(isset($singleline) && $singleline)
			 +singleline
		@endif
	">{{ $gig->description }}</p>
	<span class="gig__reward"><i class="fa fa-star"></i> Reward: {{ $hub->membership->applyPointsMultiplier($gig->points) }} points
		@if(!empty($gig->rewards))
			<a data-internal="true" class="gig__reward__other" href="{{ $gig->getDetailsUrl($hub) }}">(+ {{ $gig->rewards->count() }} more)</a>
		@endif
	</span>
	@if($gig->relationLoaded('engagement') && !is_null($gig->engagement))
		@include('partials.components.button', ['style' => 'primary', 'link' => $gig->engagement->getDetailsUrl($hub, ['tab' => 'activity']), 'text' => 'Messages', 'extraClasses' => 'gig__view --small'])
	@else
		@include('partials.components.button', ['style' => 'primary', 'link' => $gig->getDetailsUrl($hub), 'text' => 'View gig', 'extraClasses' => 'gig__view --small'])
	@endif
	@if($gig->relationLoaded('engagements'))
		<div class="gig__engagements itemlist">
			<ul>
				@foreach($gig->engagements as $i => $engagement)
					@if(!is_null($engagement->influencer))
						<li class="itemlist__item engagement">
							<h3 class="engagement__name"><a data-internal="true" href="{{ $engagement->influencer->getProfileUrl($hub) }}">{{ $engagement->influencer->name }}</a></h3>
							<div class="engagement__influencer avatar --circle">
								<a data-internal="true" href="{{ $engagement->influencer->getProfileUrl($hub) }}"><img src="{{ $engagement->influencer->profile_picture_small }}" alt="" /></a>
							</div>
							<a data-internal="true" class="engagement__view" href="{{ $engagement->getDetailsUrl($hub, ['tab' => 'activity']) }}">view</a>
						</li>
					@endif
				@endforeach
			</ul>
		</div>
	@endif
</li>